# Tracking Consent

Tracking Consent is a GDPR-compliant tool set to disable or re-enable tracking. It contains an information message to allow or disallow tracking and does either enable or disable tracking.

## Requirements

* PHP 5.6
* WordPress 4.8.6 or higher

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
