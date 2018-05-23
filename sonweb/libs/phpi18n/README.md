# PHP i18n
This is a simple i18n class for PHP. Nothing fancy, but fast, because it uses caching and it is easy to use. Try it out!

Some of its features:

* Translation strings in `.ini`/`.properties`, `.json` or `.yaml` format
* Caching
* Simple API: `L::category_stringname`
* Built-in support for [vsprintf](http://php.net/manual/en/function.vsprintf.php) formatting: `L::name($par1)`
* Automatic user language detection
* Simplicity ;)

## Requirements

* Write permissions in cache directory
* PHP 5.2 and above
* PHP SPL extension (installed by default)

## Setup

There's a usable example in the `example.php` file. You just have to follow these easy five steps:

### 1. Create language files

To use this class, you need to create translation files with your translated strings. They can be `.ini`/`.properties`, `.json` or `.yaml` files. This could look like this:

`lang_en.ini` (English)

```ini
greeting = "Hello World!"

[category]
somethingother = "Something other..."
```

`lang_de.ini` (German)

```ini
greeting = "Hallo Welt!"

[category]
somethingother = "Etwas anderes..."
```

Save both files in the directory you will set in step 4.
The files must be named according to the filePath setting, where '{LANGUAGE}' will be replaced by the user's language, e.g. 'en' or 'de'.

### 2. Include the class

```php
<?php
	require_once 'i18n.class.php';
?>
```

### 3. Initialize the class
```php
<?php
	$i18n = new i18n();
?>
```

### 4. Set some settings if necessary

The possible settings are:

* Language file path (default: `./lang/lang_{LANGUAGE}.ini`)
* Cache file path (default: `./langcache/`)
* The fallback language, if no one of the user languages is available (default: `en`)
* A 'prefix', the compiled class name (default `L`)
* A forced language, if you want to force a language (default: none)
* The section seperator: this is used to seperate the sections in the language class. If you set the seperator to `_abc_` you could access your localized strings via `L::category_abc_stringname` if you use categories in your ini. (default: `_`)
* Merge keys from the fallback language into the current language

```php
<?php
	$i18n->setCachePath('./tmp/cache');
	$i18n->setFilePath('./langfiles/lang/lang_{LANGUAGE}.ini'); // language file path
	$i18n->setFallbackLang('en');
	$i18n->setPrefix('I');
	$i18n->setForcedLang('en') // force english, even if another user language is available
	$i18n->setSectionSeperator('_');
	$i18n->setMergeFallback(false); // make keys available from the fallback language
?>
```

#### Shorthand

There is also a shorthand for that: you can set all settings in the constructor.

```php
<?php
	$i18n = new i18n('lang/lang_{LANGUAGE}.ini', 'langcache/', 'en');
?>
```

The (all optional) parameters are:

1. the language file path (the ini files)
2. the language cache path
3. fallback language
4. the prefix/compiled class name

### 5. Call the `init()` method to load all files and translations

Call the `init()` file to instruct the class to load the appropriate language file, load the cache file or generate it if it doesn't exist and make the `L` class available so you can access your localizations.

```php
<?php
	$i18n->init();
?>
```

### 6. Use the localizations

To call your localizations, simply use the `L` class and a class constant for the string.

In this example, we use the translation string seen in step 1.

```php
<?php
	echo L::greeting;
	// If 'en' is applied: 'Hello World'
	
	echo L::category_somethingother;
	// If 'en' is applied: 'Something other...'
	
	echo L::last_modified("today");
	// Could be: 'Last modified: today'
	
	echo L($string);
	// Outputs a dynamically chosen static property
	
	echo L($string, $args);
	// Same as L::last_modified("today");
	
?>
```

As you can see, you can also call the constant as a function. It will be formatted with [vsprintf](http://php.net/manual/en/function.vsprintf.php).

Also, like in the two last examples, a helper function with the same name as the class makes it easier to dynamically access the constants if ever needed.

Thats it!

## How the user language detection works

This class tries to detect the user's language by trying the following sources in this order:

1. Forced language (if set)
2. GET parameter 'lang' (`$_GET['lang']`)
3. SESSION parameter 'lang' (`$_SESSION['lang']`)
4. HTTP_ACCEPT_LANGUAGE (can be multiple languages) (`$_SERVER['HTTP_ACCEPT_LANGUAGE']`)
5. Fallback language

php-i18n will remove all characters that are not one of the following: A-Z, a-z or 0-9 to prevent [arbitrary file inclusion](https://en.wikipedia.org/wiki/File_inclusion_vulnerability).
After that the class searches for the language files. For example, if you set the GET parameter 'lang' to 'en' without a forced language set, the class would try to find the file `lang/lang_en.ini` (if the setting `langFilePath` was set to default (`lang/lang_{LANGUAGE}.ini`)).
If this file doesn't exist, php-i18n will try to find the language file for the language defined in the session variable and so on.

### How to change this implementation

You can change the user detection by extending the `i18n` class and overriding the `getUserLangs()` method:

```php
<?php
	require_once 'i18n.class.php';
	class My_i18n extends i18n {

		public function getUserLangs() {
			$userLangs = new array();

			$userLangs[] = $_GET['language'];

			$userLangs[] = $_SESSION['userlanguage'];

			return $userLangs;
		}

	}

	$i18n = new My_i18n();
	// [...]
?>
```

This very basic extension only uses the GET parameter 'language' and the session parameter 'userlanguage'.
You see that this method must return an array.

**Note that this example function is insecure**: `getUserLangs()` also has to escape the results or else i18n will [include arbitrary files](https://en.wikipedia.org/wiki/File_inclusion_vulnerability). The default implementation is safe.

## Fork it!

Contributions are always welcome.
