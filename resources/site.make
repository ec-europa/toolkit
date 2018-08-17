; ===================
; This file is intended as an EXAMPLE.
; Copy it to resources/site.make to include it in your builds.
; ===================

api = 2
core = 7.x

; ===================
; Contributed modules
; ===================

projects[apachesolr_realtime][subdir] = "contrib"
projects[apachesolr_realtime][version] = "1.4"

projects[apachesolr_sort][subdir] = "contrib"
projects[apachesolr_sort][version] = "1.0"

projects[autocomplete_deluxe][subdir] = "contrib"
projects[autocomplete_deluxe][version] = "2.3"

projects[default_image_ft][subdir] = "contrib"
projects[default_image_ft][version] = "1.6"

projects[facetapi_bonus][subdir] = "contrib"
projects[facetapi_bonus][version] = "1.2"

projects[field_collection][subdir] = "contrib"
projects[field_collection][version] = "1.0-beta11"

projects[field_formatter_css_class][subdir] = "contrib"
projects[field_formatter_css_class][version] = "1.3"

projects[field_group_easy_responsive_tabs][subdir] = "contrib"
projects[field_group_easy_responsive_tabs][version] = "1.2"

projects[imagecache_actions][subdir] = "contrib"
projects[imagecache_actions][version] = "1.8"

projects[menu_admin_per_menu][subdir] = "contrib"
projects[menu_admin_per_menu][version] = "1.1"

projects[migrate_extras][subdir] = "contrib"
projects[migrate_extras][version] = "2.5"

projects[profile2][subdir] = "contrib"
projects[profile2][version] = "1.4"

projects[realname][subdir] = "contrib"
projects[realname][version] = "1.3"
; Issue #2782711 | Warning: Invalid argument supplied for foreach() in field_view_mode_settings()
; https://drupal.org/node/2782711
projects[realname][patch][] = "https://www.drupal.org/files/issues/realname-fix-user-view-warning-2782711-4-D7.patch"
; Issue #1239478 | How to display a user's username (not real name) in Views once RealName is enabled?
; https://drupal.org/node/1239478
projects[realname][patch][] = "https://www.drupal.org/files/issues/realname-views-username-field-1239478-93.patch"

projects[replicate][subdir] = "contrib"
projects[replicate][version] = "1.2"
; Issue #2884941 | Replicate doesn't work properly with workbench_moderation module
; https://drupal.org/node/2884941
projects[replicate][patch][] = "https://www.drupal.org/files/issues/replicate-not-working-with-workbench_moderation-2884941-5-7.1.patch"

projects[role_export][subdir] = "contrib"
projects[role_export][version] = "1.0"

projects[search_api][subdir] = "contrib"
projects[search_api][version] = "1.24"

projects[search_api_solr][subdir] = "contrib"
projects[search_api_solr][version] = "1.12"

projects[search_api_view_modes][subdir] = "contrib"
projects[search_api_view_modes][version] = "1.2"

projects[term_reference_tree][subdir] = "contrib"
projects[term_reference_tree][version] = "1.11"
; Issue #1514794 | i18n compatibility
; https://drupal.org/node/1514794
projects[term_reference_tree][patch][] = "https://www.drupal.org/files/i18n_compatibility_rerolled-1514794-27.patch"
; Issue #2271719 | Broken when using jQuery 1.9 and 1.10
; https://drupal.org/node/2271719
projects[term_reference_tree][patch][] = "https://www.drupal.org/files/issues/term_reference_tree-fix_jquery_1.9%2B-2271719-3.patch"
; Issue #2803643 | Show taxonomy tree as plain text
; https://drupal.org/node/2803643
projects[term_reference_tree][patch][] = "https://www.drupal.org/files/issues/tree_as_plain_text-2803643-3.patch"

projects[views_bulk_operations][subdir] = "contrib"
projects[views_bulk_operations][version] = "3.4"
; Issue #1207348 | Integrate multi-page selection
; https://drupal.org/node/1207348
projects[views_bulk_operations][patch][] = "https://www.drupal.org/files/issues/vbo-multipage_selection-1207348-62.patch"

projects[views_field_view][subdir] = "contrib"
projects[views_field_view][version] = "1.2"

projects[views_show_more][subdir] = "contrib"
projects[views_show_more][version] = "2.0"

; =========
; Libraries
; =========
; Bootstrap Toggle 2.2.2
libraries[bootstrap_toggle][download][type] = get
libraries[bootstrap_toggle][download][url] = https://github.com/minhur/bootstrap-toggle/archive/master.zip
libraries[bootstrap_toggle][directory_name] = bootstrap_toggle
libraries[bootstrap_toggle][destination] = libraries

; DataTables 1.10.12
libraries[datatable][download][type] = get
libraries[datatable][download][url] = https://datatables.net/releases/DataTables-1.10.12.zip
libraries[datatable][directory_name] = datatable
libraries[datatable][destination] = libraries

; Easy Responsive Tabs to Accordion 1.2.2
libraries[easy-responsive-tabs][download][type] = get
libraries[easy-responsive-tabs][download][url] = https://github.com/samsono/Easy-Responsive-Tabs-to-Accordion/archive/1.2.2.zip
libraries[easy-responsive-tabs][directory_name] = easy-responsive-tabs
libraries[easy-responsive-tabs][destination] = libraries

; ======
; Themes
; ======