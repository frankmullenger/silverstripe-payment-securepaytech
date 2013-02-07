# Secure Pay Tech Payment

## Maintainer Contacts

*  Frank Mullenger

## Requirements

* SilverStripe 3.*
* SilverStripe Payment 1.*

## Documentation

### Usage Overview

This module provides Secure Pay Tech payment support for the SilverStripe Payment module. This integration uses Secure Pay Tech's [Hosted Payment Page](http://www.securepaytech.com/developers/documentation/) to process the payment which basically requires a form to be submitted directly to the Secure Pay Tech servers. 

This extra form that submits directly to their servers is displayed on the SecurePayTechConfirmation.ss template, so you can customise the content on that page with that file.

### Installation guide
  Add to mysite/_config:
  
  	SecurePayTechGateway: 
		  live:
		    merchant_id: ''
		  dev:
		    merchant_id: 'TESTDIGISPL1'

The dev account is a general test account which should work straight off the bat.