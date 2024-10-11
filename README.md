# Ruroc_GDPR

This is a Magento 2 technical test module

Ruroc\GDPR module allows customers to see what data the store collects about them and to request to be 'Forgotten' via a
'Right to be forgotten' request

The email sent to the store owner that is configured via the module config does not send any customer data only a `CustomerId`
this ID is then used to generate a link to visit the customer via the customer edit admin

## Structure

[Learn about a typical file structure for a Magento 2 module]
(https://developer.adobe.com/commerce/php/development/build/component-file-structure/).

## Config

Config Location:

`Stores` -> `Configuration` -> `Ruroc` -> `GDPR`

Default Values:

`Enable` -> `Yes`

`Send Request To Email Address` -> `demo@email.com`
