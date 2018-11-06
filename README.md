# Tracking Consent

Tracking Consent is a GDPR-compliant tool set to disable or re-enable tracking in WordPress. It contains an information message to allow or disallow tracking and does either enable or disable tracking.

## Requirements

* PHP 5.6
* WordPress 4.9.6 or higher

## Installation

1. Upload the plugin files to the `/wp-content/plugins/tracking-consent` directory.
1. Minify the JavaScript in `assets/js` to `assets/js/gdpr-notice.min.js`.
1. Compile the SCSS in `assets/style/scss` to `assets/style/style.css` and the minified version to `assets/style/style.min.css`.
1. Activate the plugin through the ‘Plugins’ screen in WordPress.
1. Use the **Design > Customizer > Tracking** section to configure the plugin.

## Configuration

You can find any configuration in the Customizer of your WordPress in the section **Tracking**.

### Tracking JavaScript code

Add any tracking JavaScript code (Google Analytics, Google Tag Manager, Matomo, Piwik etc.) in this field. JavaScript from a different location is not supported by Tracking Consent.

### Discreet screen

Change the default fullscreen design of the consent modal window to a more discreet design with a small content area at the bottom of the page.

## Supported `onclick` events

Currently supported onclick events:

* Google Analytics/Tag Manager
* Matomo/Piwik

`onclick` content with these event tracking scripts are automatically replaced if the user doesn’t want to be tracked or didn’t decide yet. If you need support for other `onclick` events, feel free to create an issue or a pull request.

## Supported languages

* English
* French
* German
* Italian
