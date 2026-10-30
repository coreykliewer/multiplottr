# Spreadsheet import and CSV export

The new local mapping workspace accepts spreadsheet text in its existing Plot Addresses box. The legacy map and production accounts are unchanged.

## Use

- Ordinary addresses, one per line, still plot directly. Commas in a street address do not automatically make it CSV.
- Paste tab-separated rows from Excel/Google Sheets, or quoted CSV, and click **Plot**. Structured data opens a sidebar preview before any marker or group is added.
- For headerless or ambiguous CSV, click **Map columns** explicitly. Select each column's meaning; use **Ignore** for unrelated columns. Adjust the separator or first-row-heading checkbox if needed.
- Review row errors/warnings, descriptions, source IDs, and the list of groups to create. Click **Plot N valid rows** to confirm. Invalid rows stay in the box for correction.
- Correct the retained text and preview again to retry. Stop plotting and duplicate-dialog cancellation keep completed markers and full remaining rows. Switching maps clears the previous queue and preview.

Supported columns: `description_format`, `icon`, `label`, `source_id`, `address`, `latitude`, `longitude`, `name`, `description`, `color`, `style`, `size`, `group`, `marker_id`, `group_id`. Common aliases include `lat`, `lng`, `lon`, `marker color`, and `marker name`.

For example, this CSV creates two new markers after confirmation:

```csv
address,latitude,longitude,name,description,color,style,size,group
,39.0241109,-77.0142811,Example point,"Plain text, including commas",#90EE90,pin,normal,Examples
"123 Example Street, Example Town",,,Example address,Geocode this address,BLUE,numbered,small,Examples
```

## Rules

- Valid latitude/longitude pairs are used directly, including zero. If coordinates are incomplete, invalid, or out of range but an address is present, the row warns that the address will be geocoded instead. Without an address, invalid coordinates are row errors.
- Omitted appearance fields use the new-marker defaults captured for the preview. Retrying unchanged retained rows keeps that snapshot, along with the column mapping and supplied details.
- Styles are pin, number/numbered, or image (the existing local star). Sizes are normal or small. Colors accept the existing named palette and three- or six-digit hex, including `#90EE90`.
- CSV/TSV descriptions are **plain text**, including quoted line breaks. HTML-looking text is displayed as text. Supported HTML requires either `description_format=html` or Legacy pipe format. Both use the existing safe HTML checks; unsupported HTML is retained for correction.
- For ordinary imports, `group_id` must identify an existing group. Versioned Multiplottr CSV exports instead treat it as a source reference and create new internal group IDs. Otherwise groups match by name (case/whitespace normalized, punctuation preserved). Ambiguous names and conflicting ID/name references are errors. New names are previewed, then groups receive application-generated IDs when their first marker is successfully added.
- Imported `marker_id` values are source metadata only. Every new marker receives an application-generated ID. Existing application/source ID conflicts and repeated incoming IDs are errors; imports never update or overwrite existing markers.
- Duplicate address/coordinate matches retain the existing Skip / Plot another marker / Stop choices. Rows are not silently deduplicated.
- Signed-out plotting limits still apply, including coordinate-only imported markers.

## Limits

Pasting and CSV/TSV file import are supported; Export CSV downloads marker interchange data. Preview shows the first 100 rows but validates all rows. The parser accepts up to 1 MB, 1,000 data rows, and 128 columns; the existing map limit is 500 markers. Pending queues are not durable across closing/reloading the page. Headerless CSV without strong spreadsheet evidence deliberately requires **Map columns**, to avoid interpreting ordinary comma-containing addresses as columns.

## Checks

From the repository root:

```powershell
node mapping-dev/legacy-paste-tests.cjs
node mapping-dev/spreadsheet-tests.cjs
node mapping-dev/tests.cjs
node mapping-dev/csv-roundtrip-tests.cjs
powershell -NoProfile -ExecutionPolicy Bypass -File mapping-dev/install.ps1
& ./local-test/browser-tools/node_modules/node/bin/node.exe mapping-dev/spreadsheet-browser-tests.cjs
```

Spreadsheet browser tests mock identity/configuration and geocoding, abort all external requests, and do not write to server account/map tables. They cover previews, mappings, quoted CSV, direct coordinates, validation, groups, duplicates, retries, cancellation, map switching, and signed-out limits. They do not certify Google's live geocoder results.

## Historical address pipe format

Select **Legacy pipe format** in the preview's **Input format** dropdown (open it using Plot or Map columns). Strong evidence, such as an HTML description or four positional fields with a group, suggests this format automatically. Recognized CSV/TSV headings and quoted CSV keep their spreadsheet interpretation. Ambiguous text, such as one literal pipe or tabs mixed with pipes, needs an explicit choice.

The fixed order is:

`Address|description (supported HTML)|marker color|group name`

Example:

`123 Main St, Boston MA|<p>Main office</p>|GREEN|Offices`

The legacy preview has no headings and its four positional mappings cannot be rearranged. One to four fields are accepted. Omitted/blank description becomes an empty description; omitted/blank color and group use the current marker defaults. Current named/hex color validation and group-name matching/creation apply. No incoming marker/group IDs are accepted in this four-field format; generated identities and the existing non-overwrite safeguards remain in place.

### Verified original source behavior

Inspected **read-only** and tested by running only isolated functions from the imported source, with stubbed controls/timers and no geocoder, AJAX, or record access:

- Root `index.php:2292` includes `scripts/main.js`.
- `scripts/main.js:215` (`batchGeocode`) changes CR and LF to backticks, changes tabs to spaces, applies literal whitespace replacements around pipes/quotes/tags, then splits records on backticks and filters empty records.
- `scripts/main.js:187` (`timedGeocoding`) uses a literal `split('|')` and passes fields 0?3. Quotes do not escape pipes. Fields beyond the fourth are not passed onward.
- That client code substitutes the address for a missing/empty description. Missing (undefined) color/group use the current controls; explicit empty color/group are passed onward. The downstream `mpcMain.v7.4.php:58` renderer also has its own default/normalization rules.
- The separate PHP paths at `index.php:1972`, `:2058`, and `:2126` use positional `explode('|', ...)` for saved/posted data. They are not CSV readers. Their record preprocessing differs from the client bulk-address path.

These are observations about the local imported source. They do not establish which historical variant is currently deployed or every legacy data producer's behavior. No production endpoint was contacted to resolve that uncertainty.

### New importer behavior and compatibility limits

- Records are separated by CRLF, CR, LF, or a literal backtick. **There is no CSV-style quoting, escaping, or multiline-record syntax.** Quotes inside a field are literal characters. Use `<br>` or separate HTML elements for description line breaks, and `&#124;` for a literal pipe inside HTML text. Quotes around a pipe do not protect it.
- More than four fields is a row error. The original row is kept, including extra pipes, rather than silently discarding fields as the old client did.
- Addresses/colors/group names are trimmed for validation. Description text is preserved verbatim, including whitespace and quotes; the importer's recovery does not repeat the original destructive HTML/whitespace cleanup. Blank records are ignored. Retained row numbers refer to the original logical record numbers.
- Omitted optional fields follow **new-map defaults**, intentionally not the old address-as-description fallback or undefined-versus-empty quirks.
- Legacy descriptions pass the existing `MapDocument.safeHtml` gate plus stricter checks that prevent the existing renderer/editor from silently dropping attributes. Supported markup includes balanced headings h1?h3, paragraphs, br, bold/strong, italic/em, lists, local links, and approved local SVG images. Allowed attributes are href on links and src/alt on images. Description values are stored unchanged as HTML and rendered through the existing description system.
- Scripts, event handlers, unsupported elements/attributes (including class/style), external media/URLs, encoded unsafe links, and malformed/unbalanced tags are row errors. Preview always shows raw descriptions as text; unsafe HTML is never inserted for preview or added as a marker. Original rows remain in the input for review/correction; there is no silent sanitization or stripping.
- Saved `~latitude,longitude|...` records are a different format and are flagged here, not treated as street addresses. Special legacy marker tokens outside the current palette (for example SMALLRED), remote marker URLs, legacy HTML outside the supported subset, and embedded literal delimiters are not automatically converted.
- Failed/unprocessed rows retain their full original fields and record separators through retries, duplicate decisions, and cancellation. Changing maps clears the old queue and blocks late callbacks as before. CSV and TSV still use proper quoting and plain-text descriptions.

Legacy tests include original-parser probes, supported/unsafe HTML, missing fields, named/hex colors, groups, ambiguous delimiters, extra pipes, raw recovery, duplicate decisions, cancellation/resume, and map switching. Browser tests continue to block external requests and use synthetic geocoding. Live historical deployment behavior remains unverified.

Advanced controls are under **Plot Addresses > Advanced**, collapsed by default. This subsection contains Map columns, Import File, Connect Google Sheet, and Export CSV. Automatic import previews still appear below Advanced, so ordinary Plot remains straightforward.

## Import File

Under Plot Addresses, click **Import File** and choose a UTF-8 CSV or TSV file. The limits (1 MB, 1,000 data rows, 500 markers per map) are shown before selection. BOMs, Unicode, quoted separators/quotes/line breaks, and empty cells are supported. File type determines the initial separator. Nothing plots until the existing preview is confirmed. Pending input is replaced only after confirmation; failed/unprocessed rows retain their columns/details for correction and retry. Use UTF-8 when saving from spreadsheet software; other encodings are rejected rather than silently garbled.

## Export CSV and reliable fresh-map reimport

**Export CSV** appears under Plot Addresses > Advanced. It exports every marker, not just currently visible groups, without geocoding. It includes original address (including null versus empty), full stored latitude/longitude precision, name, description and explicit text/html format, color, style, size, local icon, side label, group name/source ID, and marker source ID. Additional format/version/presence columns preserve optional appearance fields and exact text values.

This is **marker interchange, not a complete map backup**. Map title/settings, shapes, group visibility, unknown extension metadata, and live sheet connections are outside its scope. Export warns about unsupported or unrepresentable details, lost extension/source metadata, or data exceeding the import size limit before downloading.

For a round trip: Export CSV, create a fresh map, Import File, review the automatically recognized columns, and confirm. Stored coordinates are used directly; the original address remains separate. Descriptions marked html pass the same strict supported-HTML gate as legacy imports. Ordinary CSV/TSV without an explicit description format remains plain text. Groups are reconstructed with new internal IDs while preserving membership relationships; marker IDs are always newly generated. Exported source IDs never authorize overwriting existing markers. Reimport into a populated map still previews source-ID conflicts and duplicate choices.

### Spreadsheet formula protection

Multiplottr version 1 CSV prefixes **every text cell with one literal apostrophe**, including cells whose originals already begin with an apostrophe. Coordinates/version/presence flags remain validated numeric/boolean data. Proper CSV quoting protects separator/newline boundaries. This prevents text beginning with =, +, -, @, tabs, or other formula-like content from becoming a formula cell. On a versioned import, exactly one protection prefix is removed, restoring original values (including whitespace, Unicode, literal apostrophes, and formula-looking text).

Keep the version/protection columns and prefixes intact. A spreadsheet application may display the prefix or remove it when editing/re-exporting; if it is removed, Multiplottr reports an encoding error instead of guessing and corrupting values. The exact round-trip guarantee is for Multiplottr's exported CSV. For manually authored files, omit the version column and map normal fields.

