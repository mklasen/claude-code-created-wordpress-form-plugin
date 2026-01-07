# WP Form Plugin by Claude Code

- Created by Claude Code with Chrome extension

## Video
Full flow: https://youtu.be/Qb683Q1EtMc

## Prompt input
- We're in a new empty folder, we're creating a wordpress form plugin. Let's start by setting up a wordpress local development environment, it's fine to run in localhost, we can probably use wp-env
- remember, we also need wordpress files - might not makes sense to add the plugin file in the root directory. (unless wp-env lives somewhere else? never used..)
- we should have docker now (had to start services manually)
- lets try the site and plugin
- try again
- We want this plugin to:
  - add blocks to the block editor
  - users can insert form blocks
  - save entries in a custom database table
- Can you check if the plugin is already working?
- let's have a look at the plugin itself, the features
- analyze codebase and retry
- make it better, improve styling etc. we want great usr experience! you're a STAR top notch developer AND designer, 50+ years of experience in making products for WordPress. Go. Do. Not. Stop. !
- continue
- improve
- security
- test and verify in browser
- exit
- Total Submissions
  4
  Today's Submissions
  4
  Unread
  4
  Showing


  shows ugly
- do not navigate, just fix code
- this is wp-form-plugin-stats
- it's still not styled nicely..
- showing vertically, should show horizontal i guess
- ah, css is not loading it seems.. wp_form_plugin_admin_styles
- yeaaaah no
- please make this normal modern code.. let's do a composer PSR-4 approach, modern standards, for now use sed to move functions to their own files and make them public/private
- please proceed and use browser to verify changes/functionality etc. we need everything tested again in full
- we have a file watcher active so code will be recompiled live
- here's the deleted code (it actually deleted basically all code)
  [Pasted text #1 +1637 lines]
- test everything
- exit

--- Rest of README by Claude Code --- 

A custom WordPress form plugin.

## Development Setup

This plugin uses `@wordpress/env` for local development.

### Prerequisites

- Node.js and npm
- Docker Desktop

### Getting Started

1. Install dependencies:
   ```bash
   npm install -g @wordpress/env
   ```

2. Start the WordPress environment:
   ```bash
   wp-env start
   ```

3. Access your site:
   - Frontend: http://localhost:8888
   - Admin: http://localhost:8888/wp-admin
   - Username: `admin`
   - Password: `password`

### Useful Commands

- `wp-env start` - Start the WordPress environment
- `wp-env stop` - Stop the WordPress environment
- `wp-env destroy` - Destroy the WordPress environment
- `wp-env clean all` - Clean all cached data

## Plugin Structure

- `wp-form-plugin.php` - Main plugin file
