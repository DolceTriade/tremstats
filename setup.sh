#!/bin/bash

echo "Tremstats Too 2.0.0 simple setup script"
echo "you will be prompted for these things:"
echo "  mysql host, username, password"
echo "  mysql name to use for Tremstats database"
echo "  ip:port of server (optional, for server status)"
echo "  full path to games.log and map pk3 files"
echo "  full path to copy website files"
echo "  full path to copy tremstats log parser"
echo ""
read -p "press enter to continue" INPUT
echo ""

if [ ! -f "sql/data.sql" ] || [ ! -f "parser/tremstats.py" ] ; then
  echo "Sorry, this script must be run from within"
  echo " the Tremstats package directory (as ./setup.sh)"
  exit
fi

#i defaults:
MYSQL_HOST="localhost"
MYSQL_USER=""
MYSQL_PASS=""
MYSQL_NAME="tremstats"

SERVER_IP="localhost:30720"
SERVER_NAME="Unnamed Server"

PATH_LOG="$HOME/.tremulous/base/games.log"
PATH_MAPS="$HOME/tremulous/base"
PATH_WEBSITE="/var/www/html"
PATH_PARSER="$HOME/tremstats"
COPY_WEBSITE="no"
COPY_PARSER="no"


STATE_FILE=setup.state

if [ -f $STATE_FILE ] ; then
  source $STATE_FILE
fi


function request {
  INPUT=""
  RESULT="${3}"
  MESSAGE="${1} (${RESULT}) ]"
  read -p "$MESSAGE" INPUT

  if [ "x$INPUT" != "x" ] ; then
    RESULT="$INPUT"
  else
    echo "using default value"
  fi
  if [ "x$RESULT" == "x" ] ; then
    echo "No value entered, aborting setup"
    exit
  fi
  echo "${2} = '${RESULT}'"
  echo ""
  return 0
}

function savestate {
  echo "MYSQL_HOST=$MYSQL_HOST" > $STATE_FILE
  echo "MYSQL_USER=$MYSQL_USER" >> $STATE_FILE
  echo "MYSQL_PASS=$MYSQL_PASS" >> $STATE_FILE
  echo "MYSQL_NAME=$MYSQL_NAME" >> $STATE_FILE

  echo "SERVER_IP=\"$SERVER_IP\"" >> $STATE_FILE
  echo "SERVER_NAME=\"$SERVER_NAME\"" >> $STATE_FILE

  echo "PATH_LOG=\"$PATH_LOG\"" >> $STATE_FILE
  echo "PATH_MAPS=\"$PATH_MAPS\"" >> $STATE_FILE
  echo "PATH_WEBSITE=\"$PATH_WEBSITE\"" >> $STATE_FILE
  echo "PATH_PARSER=\"$PATH_PARSER\"" >> $STATE_FILE
  echo "COPY_WEBSITE=\"$COPY_WEBSITE\"" >> $STATE_FILE
  echo "COPY_PARSER=\"$COPY_PARSER\"" >> $STATE_FILE
}

echo "MYSQL information"
echo ""

request "Enter mysql hostname" "mysql hostname" "$MYSQL_HOST"
MYSQL_HOST=$RESULT

request "Enter mysql username" "mysql username" "$MYSQL_USER"
MYSQL_USER=$RESULT

request "Enter mysql password" "mysql password" "$MYSQL_PASS"
MYSQL_PASS=$RESULT

request "Enter mysql database" "mysql database name" "$MYSQL_NAME"
MYSQL_NAME=$RESULT

echo "Tremulous server information"
echo ""

request "Enter path to games.log" "games.log" "$PATH_LOG"
PATH_LOG=$RESULT

request "Enter path to map pk3 files" "map pk3 files" "$PATH_MAPS"
PATH_MAPS=$RESULT

request "Enter tremulous server ip" "trem server ip" "$SERVER_IP"
SERVER_IP=$RESULT

request "Enter tremulous server name" "trem server name" "$SERVER_NAME"
SERVER_NAME=$RESULT

echo "INSTALLATION paths"
echo ""

request "Enter path to install website files" "www path" "$PATH_WEBSITE"
PATH_WEBSITE=$RESULT

echo "note: if you do not have permissions to write $PATH_WEBSITE,"
echo "say no here, and copy the 'web' directory to the desired location"

request "Do you want to install website files [yes/no]" "copy www" "$COPY_WEBSITE"
COPY_WEBSITE=$RESULT

request "Enter path to install tremstats.py" "parser path" "$PATH_PARSER"
PATH_PARSER=$RESULT

echo "note: if you do not have permissions to write $PATH_PARSER,"
echo "say no here, and copy the 'parser' directory to the desired location"

request "Do you want to install tremstats.py files [yes/no]" "copy parser" "$COPY_PARSER"
COPY_PARSER=$RESULT

savestate

echo "Ok, I am now about to verify the mysql information entered above"
echo "by logging in with mysql and running a STATUS command"
read -p "Press enter to continue" INPUT

echo "testing mysql information..."
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASS -e STATUS
MYSQL_OK=$?
if [ "$MYSQL_OK" != "0" ] ; then
  echo "ERROR communicating with mysql host, please check entered values"
  echo "installation aborted"
  exit
fi

echo "Good! mysql seems to be ok"
echo ""
echo "I will now (optionally) create and initialize the database '$MYSQL_NAME'"
echo "NOTE: if a database named '$MYSQL_NAME' exists, this will fail"
echo "Enter 'yes' to create the database '$MYSQL_NAME' or 'no' to skip this step."
read -p "Create database ? [yes/no] ]" INPUT
if [ "$INPUT" == "yes" ] ; then
  echo "Creating database..."
  mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASS -e "CREATE DATABASE $MYSQL_NAME"
  MYSQL_OK=$?
  if [ "$MYSQL_OK" != "0" ] ; then
    echo "I got an error trying to create '$MYSQL_NAME'"
    echo "installation aborted"
    exit
  fi
else
  echo " ** that was not a 'yes', database creation SKIPPED."
fi

echo "Enter 'yes' to setup the database structure for '$MYSQL_NAME' or 'no' to skip this step."
echo "NOTE: by saying 'yes', any existing tremstats data in database '$MYSQL_NAME' will be lost"
read -p "Setup database structure ? [yes/no] ]" INPUT
if [ "$INPUT" == "yes" ] ; then
  echo "setting up essential data..."
  mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASS $MYSQL_NAME < sql/structure.sql
  mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASS $MYSQL_NAME < sql/data.sql
  echo "database is ready, continuing"
else
  echo " ** that was not a 'yes', database creation SKIPPED."
fi

echo ""

PARSER_CONFIG="parser/config.py"
echo "updating file '$PARSER_CONFIG'..."
sed --in-place=.bak -e "s/CONFIG\['MYSQL_HOSTNAME'\] = '[^']*\?'/CONFIG\['MYSQL_HOSTNAME'\] = '$MYSQL_HOST'/g" $PARSER_CONFIG
sed --in-place -e "s/CONFIG\['MYSQL_USERNAME'\] = '[^']*\?'/CONFIG\['MYSQL_USERNAME'\] = '$MYSQL_USER'/g" $PARSER_CONFIG
sed --in-place -e "s/CONFIG\['MYSQL_PASSWORD'\] = '[^']*\?'/CONFIG\['MYSQL_PASSWORD'\] = '$MYSQL_PASS'/g" $PARSER_CONFIG
sed --in-place -e "s/CONFIG\['MYSQL_DATABASE'\] = '[^']*\?'/CONFIG\['MYSQL_DATABASE'\] = '$MYSQL_NAME'/g" $PARSER_CONFIG
sed --in-place -e "s@CONFIG\['GAMES_LOG'\] *\?= '[^']*\?'@CONFIG\['GAMES_LOG'\] = '$PATH_LOG'@g" $PARSER_CONFIG
sed --in-place -e "s@CONFIG\['PK3_DIR'\] *\?= '[^']*\?'@CONFIG\['PK3_DIR'\] = '$PATH_MAPS'@g" $PARSER_CONFIG

WEB_CONFIG="web/core/config.inc.php"
echo "updating file '$WEB_CONFIG'..."
sed --in-place=.bak -e "s@define('MYSQL_HOSTNAME', '[^']*\?')@define('MYSQL_HOSTNAME', '$MYSQL_HOST')@g" $WEB_CONFIG
sed --in-place -e "s@define('MYSQL_USERNAME', '[^']*\?')@define('MYSQL_USERNAME', '$MYSQL_USER')@g" $WEB_CONFIG
sed --in-place -e "s@define('MYSQL_PASSWORD', '[^']*\?')@define('MYSQL_PASSWORD', '$MYSQL_PASS')@g" $WEB_CONFIG
sed --in-place -e "s@define('MYSQL_DATABASE', '[^']*\?')@define('MYSQL_DATABASE', '$MYSQL_NAME')@g" $WEB_CONFIG
sed --in-place -e "s@define('TREMULOUS_ADDRESS', '[^']*\?')@define('TREMULOUS_ADDRESS', '$SERVER_IP')@g" $WEB_CONFIG
sed --in-place -e "s@define('TREMULOUS_SERVER_NAME', '[^']*\?')@define('TREMULOUS_SERVER_NAME', '$SERVER_NAME')@g" $WEB_CONFIG


if [ "$COPY_WEBSITE" == "yes" ] ; then
  echo "Copying 'web' directory to $PATH_WEBSITE"
  mkdir -pv "$PATH_WEBSITE"
  cp -r web/* "$PATH_WEBSITE"
else
  echo "Copying 'web' skipped by request"
  echo "  ** update $PATH_WEBSITE/core/config.in.php manually"
fi
echo " ** NOTE: if you wish to make the tremstats package available from your website copy the tremstats_too_X_X_X.zip file into $PATH_WEBSITE"

if [ "$COPY_PARSER" == "yes" ] ; then
  echo "Copying 'parser' to $PATH_PARSER"
  mkdir -pv "$PATH_PARSER"
  cp -r parser/* "$PATH_PARSER/."
else
  echo "Copying 'parser' skipped by request"
  echo "  ** update $PATH_PARSER/config.py manually"
fi

echo ""
echo "important locations:"
echo " parser config = $PATH_PARSER/config.py"
echo "               *default is in parser/config.py.default"
echo " web configs   = $PATH_WEBSITE/core/config.inc.php"
echo "               *default is in web/core/config.inc.php.default"
echo " games.log     = $PATH_LOG"
echo " maps          = $PATH_MAPS"
echo " database name = $MYSQL_NAME"
echo ""
echo "Tremstats Too setup script complete."

