# Ryby/Mail

Simple file mailer for nette.

[![Build Status](https://travis-ci.org/Ryby/Mail.svg?branch=master)](https://travis-ci.org/Ryby/Mail)
[![Coverage Status](https://coveralls.io/repos/github/Ryby/Mail/badge.svg?branch=master)](https://coveralls.io/github/Ryby/Mail?branch=master)

#### Download via composer

```
composer require ryby/mail --dev

```
#### Usage

```
extensions:
  fileMailer: Ryby\Mail\DI\FileMailerExtension

# Optional
fileMailer:
  tempDir: %tempDir%/my-mails

```
