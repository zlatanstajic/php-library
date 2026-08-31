project = "PHP Library"
copyright = "2026, Zlatan Stajic"
author = "Zlatan Stajic"

extensions = ["myst_parser"]

source_suffix = {
    ".md": "markdown",
}

exclude_patterns = ["_build", "Thumbs.db", ".DS_Store"]

html_theme = "sphinx_rtd_theme"
html_logo = "../assets/logos/logo-white.png"
html_favicon = "../assets/img/phplibrary-icon.png"
html_static_path = ["_static"]
html_css_files = ["custom.css"]
html_theme_options = {
    "logo_only": False,
    "prev_next_buttons_location": "bottom",
    "style_external_links": False,
    "vcs_pageview_mode": "blob",
}

html_context = {
    "display_github": True,
    "github_user": "zlatanstajic",
    "github_repo": "php-library",
    "github_version": "master",
    "conf_py_path": "/docs/",
}

myst_heading_anchors = 3
