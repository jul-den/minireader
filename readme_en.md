# 🙏 Acknowledgments

This project is a fork of the original [MiniReader](https://github.com/circlejourney/MiniReader) by [circlejourney](https://github.com/circlejourney). Many thanks to the original author for creating such a clean and useful tool!

> **Русская версия:** [README.md](README.md)

---

# MiniReader: A minimal(ish) templater for self-publishing a collection of stories

> **Note:** This is a fork of the original [MiniReader](https://github.com/circlejourney/MiniReader) with added features: disclaimer system, multilingual support (localization), and image handling.

A PHP templater for self-publishing a collection of stories. It generates a listing of all stories in the gallery that can be viewed at the reader's homepage. It supports single-chaptered and multi-chaptered works, as well as alternate chapter naming (e.g. naming chapters as "Episodes").

The entire MiniReader directory can be uploaded as-is to a web host and it should run out of the box. Two examples are included to demonstrate folder and `story.json` structure. A live example can be viewed at [circlejourney.net/read](https://circlejourney.net/read).

# Quick start

To add a story to the collection, add a sub-folder in folder `stories` with a unique name with no spaces. To this folder, upload the contents of individual chapters (without titles) as numbered HTML files, e.g. `1.html`, `2.html`, `3.html`... Finally, upload a `story.json` file to the same folder (details on how to format it are below).

The directory structure should look like this:

```
/
├── disclaimer.php
├── getimg.php
├── getstory.php
├── minireader.php
public/
├── index.php          # Main entry point
├── .htaccess          # URL rewriting for images
├── css/
│   ├── style.css      # Base styles
│   └── style_ai.css   # AI-specific styles (disclaimer, etc.)
├── js/
│   ├── jquery-3.3.1.min.js
│   └── lightbox.js     # Image lightbox
stories/
├── your-story-id/
│   ├── story.json
│   ├── 1.html
│   ├── 2.html
│   └── image.jpg      # Can be referenced in HTML
locals/
├── en.php             # English translations
├── ru.php             # Russian translations
├── disclaimer_en.json # English disclaimer base
└── disclaimer_ru.json # Russian disclaimer base
```

To view a preview, install [XAMPP](https://www.apachefriends.org/) and add the `php` sub-directory from the install folder to your PATH variable. After that, start a PHP server by running the command `php -S localhost:8000` inside the MiniReader directory, and you can view the MiniReader inside your browser at the URL `localhost:8000`.

> Note: The web server must point to the public/ folder as its document root. The included .htaccess file handles URL rewriting for images.

# Files

`public/index.php` is the main entry point. It detects the user's language and loads the appropriate translation files, then includes `minireader.php` (the core reader).

`minireader.php` is the core reader. Add a search query i.e. `?story=<story-ID>&c=<chapter-number>` to the URL to fetch specific stories and chapters.

`getstory.php` is a helper that scans directories, filters for selected chapters, and retrieves story/chapter content and metadata.

`disclaimer.php` loads the disclaimer base from `locals/disclaimer_{lang}.json`, merges story-level and chapter-level disclaimer overrides from `story.json`, and returns the final disclaimer sections for rendering.

`getimg.php` handles image requests for story illustrations. When images are referenced in HTML as `/img/story-id/image.jpg`, the `public/.htaccess` file routes the request to `public/index.php`, which serves the corresponding image from the `stories/your-story-id/` folder.

`public/css/style.css` contains all the base styling for the story. It includes a `.dark` class that is responsible for setting all dark mode styling.

`public/css/style_ai.css` contains AI-specific styles, including formatting for the disclaimer blocks.

`public/.htaccess` redirects requests for images (jpg, jpeg, png, gif, webp), allowing you to reference story images in your HTML as `<img src="/img/story-id/image.jpg">`.

# story.json

`story.json` contains all metadata for the story in that folder.

- `"title": "Your Story Title"`: (required) The display title that is shown in the HTML page title and the story header.
- `"storyID": "your-story-id"`: (required) Unique story ID, containing letters, numbers, hyphens and underscores. This should be the same as the folder name.
- `"homepage": "https://story.website.com/"`: (optional) A URL for readers to find out more about the story.
- `"enumerated": true|false`: (optional) Whether the chapters should be displayed with numbers and nomenclature in their titles. Defaults to true if unspecified. If set to false, all chapters titles will be displayed without the "Chapter X:" label in front.
- `"chapterNomenclature": "Chapter"`: The terminology used for chapters. Can be useful if you want the chapters to be called "episodes", for example. Defaults to "Chapter" if unspecified.
- `"description": "description of story"`: (optional) A description of the story for the website's meta description.
- **(NEW)** `"disclaimer": ...`: (optional) Story-level disclaimer override (see Disclaimer System below).
- `"chapters": [ ... ]`: Array of chapter objects. The web app can only "see" chapters that are added here, so HTML files that don't have a corresponding chapter object added here cannot be accessed. Chapter object properties:
  - `"title": "Chapter Title"`: Title of the chapter
  - `"enumerated": true|false`: (optional) Whether the chapter should be displayed with a number and nomenclature in its title. Defaults to true. If set to false, the chapter title will be displayed without the "Chapter X:" label in front.
  - **(NEW)** `"disclaimer": ...`: (optional) Chapter-level disclaimer override (see Disclaimer System below).

# Disclaimer System **(NEW)** 

MiniReader now supports a flexible disclaimer system that can be configured at both the story and chapter levels.

## Base Disclaimer File

Place/edit `disclaimer_{lang}.json` in the `locals/` folder (e.g., `locals/disclaimer_en.json` for English, `locals/disclaimer_ru.json` for Russian). The file defines all available disclaimer sections. See `locals/disclaimer_en.json` for example.


## Story-Level Disclaimer

Add a `disclaimer` field to your `story.json` to configure which sections appear:

```json
{
    "title": "Your Story",
    "storyID": "your-story-id",
    "disclaimer": {
        "fictional": true,
        "ai_generated": true,
        "content_warning": true,
        "author_responsibility": false,
        "owner_responsibility": true,
        "prompt": "Your full prompt text here...",
        "custom_section_any_text": {
            "title": "Notice",
            "text": "New story with love."
        }
    },
    "chapters": [ ... ]
}
```

**Configuration options:**
- `true`: Use the section as defined in the base disclaimer file
- `false`: Hide this section
- `{ "title": "...", "text": "..." }`: Override the section's title and/or text

## Chapter-Level Disclaimer

You can override the disclaimer for a specific chapter by adding a `disclaimer` field to a chapter object:

```json
{
    "chapters": [
        {
            "title": "Prologue",
            "enumerated": false,
            "disclaimer": false
        },
        {
            "title": "Chapter 1",
            "disclaimer": {
                "content_warning": false,
                "prompt": "Specific prompt for this chapter..."
            }
        },
        {
            "title": "Chapter 2",
            "disclaimer": {
                "ai_generated": {
                    "title": "🧠 Generated by Claude",
                    "text": "This chapter was created using Claude 3.5 Sonnet."
                },
                "custom_section_any_text": {
                    "title": "Notice",
                    "text": "This chapter was written by Claude 3.5 Sonnet."
                }
            }
        },
        {
            "title": "Epilogue",
            "enumerated": false
        }
    ]
}
```

**Chapter disclaimer priority:**
1. If the chapter's `disclaimer` is `false`, the disclaimer is completely hidden for that chapter
2. If the chapter's `disclaimer` is an array/object, it merges with the story-level disclaimer (chapter values override story values)
3. If no chapter disclaimer is specified, the story-level disclaimer is used

## Loading Disclaimers

The system automatically loads the appropriate language file based on the user's browser language settings. The available languages and the default language are defined in `public/index.php`.


# Images

Stories can include images. Place images in `stories/your-story-id/image_name.jpg` and reference them in your HTML as:

```html
<img src="/img/your-story-id/image_name.jpg" alt="Description">
<img class="profile-thumb" src="/img/your-story-id/image_name.jpg" alt="with lightbox">
```

The `.htaccess` file in `public/` handles routing these requests. Images can be opened in a lightbox using the included `lightbox.js` by `<img class="profile-thumb" ...>`.

# Localization

The system automatically detects the user's preferred language from browser settings and loads the corresponding translation files from `locals/`.

Available languages are configured in `public/index.php`. If the user's language is not available, the default (first in the list) is used.

Each language file (`locals/xx.php`) defines translation strings. See `locals/en.php` for a complete example.

To create a new language:
1. Create (or copy an existing file) `locals/yourlang.php` with translation strings
2. Create (or copy an existing file) `locals/disclaimer_yourlang.json` with the disclaimer sections in that language
3. Add the language code to the available languages array in `public/index.php`

