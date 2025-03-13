# Crawlaco Translations

This directory contains translation files for the Crawlaco plugin.

## Available Languages

- Persian (فارسی) - `fa_IR`

## How to Use Translations

1. Make sure you have Poedit installed on your system (https://poedit.net/)
2. Run the `compile-translations.bat` script to compile the translation files
3. The compiled `.mo` files will be automatically generated
4. WordPress will automatically load the appropriate translation based on your site's language setting

## Adding New Translations

To add a new translation:

1. Create a new `.po` file with the appropriate language code (e.g., `fr_FR.po` for French)
2. Copy the structure from `fa_IR.po`
3. Translate all the strings
4. Run the compilation script to generate the `.mo` file

## Translation Guidelines

- Keep translations natural and fluent in the target language
- Maintain the same formatting and placeholders (%s, %d, etc.)
- Test the translations in the actual plugin interface
- Consider cultural differences and local conventions

## Support

For translation issues or questions, please contact the Crawlaco support team. 