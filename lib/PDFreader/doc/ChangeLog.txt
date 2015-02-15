v0.1.3
- Fixed bug in which Marked Content operators would sometimes appear in form fields
- Removed "exit" and "die" statements for PEAR compliance
- Added error trap for absence of `gzip`
- Implemented package-specific Exceptions
- Name changed to File_PDFreader for PEAR compliance

v0.1.2
- Fixed known bug with character mapping non-standard fonts
- Added limited support for text matrices

v0.1.1
- Fixed a bug in which a string without ET operator would have zero length
- Added support for hexadecimal strings embedded in normal strings
- Fixed a bug in which some line breaks are ignored
- Standardized regular expressions for primitive data types as constants
- Shortened lines and adjusted switch/case indents for PEAR compliance
- Moved extractText routines from PDFobject class to PDFpage class in order
       to assemble multiple content streams
- Refactored to use a single PDFdecoder instance for memory efficiency

v0.1.0
- Initial proposal
- Included basic support for text and form field extraction
- Some known bugs with character mapping non-standard fonts