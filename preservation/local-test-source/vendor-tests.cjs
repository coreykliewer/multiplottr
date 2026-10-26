const fs=require('fs'),vm=require('vm'),assert=require('assert'),crypto=require('crypto');
const file='local-test/app/scripts/vendor/markermanager-1.0.29/markermanager.umd.js';
const source=fs.readFileSync(file,'utf8');
assert.equal(crypto.createHash('sha256').update(source).digest('hex'),'136bcf1f1cb0f8a50f99d1d9d7a52d2cc883916f001cccd5178b3d298d97c78d');
// Verify the real pinned bundle's UMD exports without making a Google API request.
const sandbox={google:{maps:{OverlayView:class{},MVCObject:class{}}}};vm.createContext(sandbox);vm.runInContext(source,sandbox);
const Manager=sandbox.google.maps.plugins.markermanager.MarkerManager;
assert.equal(typeof Manager,'function');
for(const name of ['addMarkers','clearMarkers','refresh','hide'])assert.equal(typeof Manager.prototype[name],'function');
assert(fs.readFileSync('local-test/app/scripts/vendor/markermanager-1.0.29/LICENSE','utf8').includes('Apache'));
console.log('Pinned MarkerManager checksum, UMD export, required method surface, and license verified. This does not test Google Maps compatibility.');
