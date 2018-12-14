# Micropub Endpoint

Made with Laravel Lumen and PHP 7.1.

# Setting Up

1. Download project from GitHub
2. `composer install`
3. Set up `.env` with your config settings
4. Deploy to your site or test locally

# Supported Static Site Generators

For now [Hugo](https://gohugo.io/) is the only static site generator that this endpoint supports.
However it's trivial to add a new provider in `app/Providers` for your specific generator of choice.
Take a look at the `HugoProvider` for an example.

## TODO

- [x] Create new post
- [ ] Update post
- [ ] Delete post
- [ ] Undelete post
- [x] Support media upload
- [ ] More tests
