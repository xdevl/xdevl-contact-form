# XdevL contact form

Wordpress plugin providing an Ajax contact form using Google recaptcha which can easily be embedded to any of your pages. You will need to register your website on the [Google recaptcha admin page](https://www.google.com/recaptcha/admin) in order to get your pair of public/private keys.

# How to

In order to build and package this plugin you will need [python3](https://www.python.org) and [composer](https://getcomposer.org/) installed locally on your machine.

Start off by downloading the plugin dependencies by running the following command at the root of the plugin directory:
```markdown
composer install
```
Once complete the plugin directory should have everything needed in order to work. If it happens to be located under your local Wordpress plugin directory, you should be able to edit any files and directly see the result on your local Wordpress installation.

To package the plugin as a zipped Wordpress plugin file and strip down any unwanted files, symply run:
```markdown
python build.py
```

# LICENSE

This plugin is licensed under [GPLV2](http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html) and powered by [Google recaptcha](https://www.google.com/recaptcha/intro/index.html)
