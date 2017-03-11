# Tableizer - WordPress Plugin

# Installation

```shell
cd wp-content/plugins
git clone https://github.com/M1nified/wp-tableizer.git tableizer
```

# About

## Simple usage

```
[tableizer category="category name"]
```

## Shortcode attributes

| Attribute    | Description
|:---          | :---
|`category`    | category to display
|`link_target` | target for all displayed link cells
|`only_rows`   | outputs only content of tbody
|`per_page`    | number of rows displayed per page
|`top`         | number of the first N rows to display

## Shortcode examples

```text
[tableizer category="category name"]

[tableizer category="category name" top="10"]

[tableizer category="category name" per_page="20"]

[tableizer category="category name" only_rows="on"]

[tableizer category="category name" link_target="_blank"]
```

# Adding content

## Cell content examples

| Type  | Input                                           | Output HTML 
|---    |---                                              | ---
|text   | `just plain text`	                              | `just plain text` 
|text   | `<button>Click me</button>`                     |	`<button>Click me</button>` 
|image  | `[Example image]http://example.com/example.png` | `<img src="http://example.com/example.png" alt="Example image">` 
|link   | `[Read more]http://example.com/full_article`    | `<a href="http://example.com/full_article">Read more</a>` 