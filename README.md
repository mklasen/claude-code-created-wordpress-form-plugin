# WP Form Plugin

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
