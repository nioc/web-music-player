# web-music-player

Web Music Player is a simple way to play music on the web.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/nioc/web-music-player/tags).

## Authors

* **[Nioc](https://github.com/nioc/)** - *Initial work*

See also the list of [contributors](https://github.com/nioc/web-music-player/contributors) who participated in this project.

## Motivation

Our aim is to provide an easy to use web player able to handle a local catalog. Some awesome projects already already exists but often with a complicated code.

## API

Please see [API description](API.md).


## Contributing

The project is open and any contribution is welcome!

To keep the code clean, we use [php-cs-fixer](http://cs.sensiolabs.org/), before commit launch this on each edited files:

```` bash
php /usr/local/bin/php-cs-fixer fix /path/to/editedFile.php -v
````
You can handle all edited files with this single line:
```` bash
cd /var/www/wmp; for file in $(git diff-index --name-only HEAD); do php /usr/local/bin/php-cs-fixer fix "$file" -v; done
````

A little how-to for github:

1. [Fork it](https://help.github.com/articles/fork-a-repo/)
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes (with a detailled message): `git commit -am 'Add an awesome feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/fa150783-5bf2-4e9d-bcee-395401edf439/mini.png)](https://insight.sensiolabs.com/projects/fa150783-5bf2-4e9d-bcee-395401edf439)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/615c9f1907364f9a8812298c11b8eb31)](https://www.codacy.com/app/nioc/web-music-player)

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE.md) file for details

## Included project

This project includes the following:
- [Font Awesome](https://github.com/FortAwesome/Font-Awesome/)
- [AngularJS](https://github.com/angular/angular.js)
- [normalize.css](https://github.com/necolas/normalize.css)
