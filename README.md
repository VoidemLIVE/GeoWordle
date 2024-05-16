
# Geo Wordle

A geography wordle-like game for guessing countries



## Features

- 5 characteristics of a country (excluding the name)
- Daily country switch (Cron job not included in repo)
- 227 different countries that can be chosen at random
- 2 Hints that can be used as you make more guesses

## Live website

![Geo Wordle](https://i.imgur.com/6vEUlkv.png)

https://geo.voidem.com
## Important Info

This repo does NOT contain sorted_countries.json or currentCountry.json. sorted_countries.json should contain the data of all countries listed. currentCountry.json will be changed when newCountry.php is run.

## Environment Variables

To run this project, you will need to add the following environment variables to your .env file

`SESS_FOLDER="/path/to/sessions/folder/"`


## License

[MIT](https://choosealicense.com/licenses/mit/)
