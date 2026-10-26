// Offline source-only preservation. Never reads private config, dumps, or data folders.
// Refuses to overwrite a preservation run. Sanitization affects copies only.
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const root = path.resolve(__dirname, '..');
const sha = b => crypto.createHash('sha256').update(b).digest('hex');
const manifest = [];
const denied = /(?:^|\/)(?:node_modules|_notes|datafiles|custom|uploads|cache|logs|Connections)(?:\/|$)|(?:^|\/)(?:db(?:\.v7\.4|-07072024)?\.php|config\.log\.php|wp-config\.php|maps\.local\.json)$/i;
const extensions = new Set(['.php','.js','.cjs','.css','.html','.htm','.json','.md','.ps1','.cmd','.yaml','.yml','.conf']);
function sanitize(s) {
  return s
    .replace(/AIza[0-9A-Za-z_-]{30,}/g, '[REDACTED_GOOGLE_KEY]')
    .replace(/ABQI[0-9A-Za-z_-]{40,}/g, '[REDACTED_LEGACY_GOOGLE_KEY]')
    .replace(/-----BEGIN [^-]*PRIVATE KEY-----[\s\S]*?-----END [^-]*PRIVATE KEY-----/g, '[REDACTED_PRIVATE_KEY]')
    .replace(/\$dbconfig\s*=\s*array\s*\([\s\S]*?\);/gi, '$dbconfig = array(); // REDACTED: configure privately')
    .replace(/((?:["']?(?:password|passwd|pwd|pass|api_?key|apikey|secret|access_token|auth_token|client_secret|smtp_password|smtpuser|smtppass|db_?user|db_?pass|db_?host|db_?name|username)["']?\s*(?:=>|:|=)\s*))["'][^"'\r\n]*["']/gi, '$1"[REDACTED_CONFIG_VALUE]"')
    .replace(/(define\s*\(\s*["'][^"']*(?:PASS|KEY|TOKEN|SECRET|DB_USER|DB_HOST|DB_NAME)[^"']*["']\s*,\s*)["'][^"'\r\n]*["']/gi, '$1"[REDACTED_CONFIG_VALUE]"')
    .replace(/((?:MYSQL|MARIADB)_(?:ROOT_PASSWORD|PASSWORD|USER|DATABASE)\s*:\s*)[^\r\n]+/g, '$1[REDACTED_CONFIG_VALUE]')
    .replace(/([?&](?:key|token|hash|signature)=)[^&\s"'<>]+/gi, '$1[REDACTED_TOKEN]')
    .replace(/https?:\/\/[^\s/"'<>]+:[^\s/@"'<>]+@/gi, 'https://[REDACTED_USERINFO]@')
    .replace(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/gi, '[REDACTED_EMAIL]')
    .replace(/\bmultip17(?:_[a-z0-9_]+)?\b/gi, '[REDACTED_ACCOUNT]')
    .replace(/\b(?:(?:\d{1,3})\.){3}\d{1,3}\b/g, ip => ['127.0.0.1','0.0.0.0'].includes(ip) ? ip : '[REDACTED_IP]');
}
function preserve(base, relative, set) {
  relative = relative.replace(/\\/g, '/');
  if (denied.test(relative)) return;
  const source = path.join(base, relative);
  const stat = fs.lstatSync(source);
  if (stat.isSymbolicLink()) return;
  if (stat.isDirectory()) {
    for (const name of fs.readdirSync(source).sort()) preserve(base, relative + '/' + name, set);
    return;
  }
  if (!extensions.has(path.extname(relative).toLowerCase()) && !/^LICENSE(?:\.[^/]*)?$/i.test(path.basename(relative))) return;
  if (/package-lock\.json$/i.test(relative)) return;
  const bytes = fs.readFileSync(source);
  if (bytes.length > 5 * 1024 * 1024 || bytes.includes(0)) return;
  const original = bytes.toString('utf8');
  const safe = sanitize(original);
  const target = path.join(__dirname, set, relative);
  fs.mkdirSync(path.dirname(target), { recursive: true });
  fs.writeFileSync(target, safe, { flag: 'wx' });
  manifest.push({set, path: relative, sourceSha256: sha(bytes), preservedSha256: sha(Buffer.from(safe)), sanitized: original !== safe});
}
for (const name of ['original-source','local-test-source']) {
  if (fs.existsSync(path.join(__dirname, name))) throw new Error('Preserved source already exists; refusing overwrite.');
}
// Root application variants only, not neighboring sites, WordPress, or customer folders.
for (const name of fs.readdirSync(root).sort()) {
  if (/^(?:index.*|mpc.*|MULTIPLOTTR.*|multipMain|checklogin(?:\.v7\.4)?|reglogin(?:\.v7\.4)?|localsession.*|download_kml.*|embed|freeusers.*|freeuserlogout.*|prousers|prologout|logout.*|resetpassword)\.php$/i.test(name) || /\.css$/i.test(name)) preserve(root, name, 'original-source');
}
for (const name of ['scripts','css','includes','class','db']) preserve(root, name, 'original-source');
const local = path.join(root, 'local-test');
for (const name of fs.readdirSync(local).sort()) {
  if (name === 'app' || name === 'config' || name === 'browser-tools') continue;
  if (/\.(php|cjs|ps1|cmd|yaml|conf)$/i.test(name) || ['Dockerfile','README.md','MAPS-DEVELOPMENT.md'].includes(name)) {
    if (name === 'Dockerfile') {
      const b = fs.readFileSync(path.join(local,name));
      fs.mkdirSync(path.join(__dirname,'local-test-source'),{recursive:true});
      fs.writeFileSync(path.join(__dirname,'local-test-source',name),sanitize(b.toString()),{flag:'wx'});
      manifest.push({set:'local-test-source',path:name,sourceSha256:sha(b),preservedSha256:sha(Buffer.from(sanitize(b.toString()))),sanitized:sanitize(b.toString())!==b.toString()});
    } else preserve(local,name,'local-test-source');
  }
}
// Same source policy for the working app, with its modified/new core files.
const app = path.join(local,'app');
for (const name of fs.readdirSync(app).sort()) {
  if (/^(?:index.*|mpc.*|MULTIPLOTTR.*|multipMain|checklogin(?:\.v7\.4)?|reglogin(?:\.v7\.4)?|localsession.*|download_kml.*|embed|freeusers.*|freeuserlogout.*|prousers|prologout|logout.*|resetpassword)\.php$/i.test(name) || /\.css$/i.test(name) || ['scripts','css','includes','class','db'].includes(name)) preserve(local,'app/'+name,'local-test-source');
}
preserve(local,'browser-tools/package.json','local-test-source');
preserve(local,'config/maps.local.example.json','local-test-source');
// Evidence identity only: generated outputs remain ignored and unchanged on disk.
const evidence = ['maps-live-results.json','maps-native-print-results.json','membership-results.json','membership-offline-results.json','browser-results.json','maps-contract-results.json','maps-verification.json'].map(name => ({path:'local-test/'+name,sha256:sha(fs.readFileSync(path.join(local,name)))}));
fs.writeFileSync(path.join(__dirname,'MANIFEST.json'),JSON.stringify({format:1,reviewDate:'2026-10-25',sourcePolicy:'Sanitized source-only copies. Original working trees untouched; no data/configuration/binary backup.',files:manifest,evidence},null,2)+'\n',{flag:'wx'});
console.log(JSON.stringify({sourceFiles:manifest.length,sanitizedFiles:manifest.filter(x=>x.sanitized).length,evidenceHashes:evidence.length}));
