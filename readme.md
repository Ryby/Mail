Mail
=============

#### Download via composer

```
composer install ryby/mail --dev

```
#### Usage

```
extensions:
  fileMailer: Ryby\Mail\DI\FileMailerExtension

# Optional
fileMailer:
  tempDir: %tempDir%/my-mails

```
