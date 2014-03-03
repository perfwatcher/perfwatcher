How to initialize postgresql database
-------------------------------------
1/ Check your pg_hba.conf file

Basic installation should contain a line like this :

# TYPE  DATABASE    USER        CIDR-ADDRESS          METHOD
[...]
local   all         all                               md5

2/ Create a user

# su - postgres
$ createuser --encrypted --no-inherit --login --pwprompt --no-createrole --no-superuser --no-createdb perfwatcher

3/ Create the database

# su - postgres
$ createdb -e --encoding=UTF8 --owner=perfwatcher perfwatcher

4/ Create the database schema

cat create.pgsql | psql -W perfwatcher perfwatcher
