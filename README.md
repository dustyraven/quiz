# Quiz
Simple quiz demo.

## Install
- Checkout the repo and run `composer install`.  
- Create MySQL/MariaDB database and user and update credentials in `class/Database.php`
- Use file `quiz.sql` to fill the database.

## Run

To run execute:  
`php quiz.php '{"userId":1}'` to get a question  
**or**  
`php quiz.php '{"userId":1,"answerId":4}'` to set an answer and to get the next question.

## Test

Execute `./test.sh`

Included tests:
- test for PSR2 ([PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer))
- test for PHP mess ([phpmd](https://phpmd.org/))
- Unit tests ([PHPUnit](https://phpunit.de/))
