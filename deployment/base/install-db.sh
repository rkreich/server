#!/bin/bash
# Database install script is based on https://github.com/kaltura/platform-install-packages

set -e
OP_DBS="kaltura kaltura_sphinx_log"
DWH_DBS="kalturadw kalturadw_ds kalturadw_bisources kalturalog"
DB_USERS="kaltura etl"
kaltura_PRIVILEGES="INSERT,UPDATE,DELETE,SELECT,ALTER,CREATE"
kaltura_USER="kaltura"
kaltura_SQL_FILES="/opt/kaltura/app/deployment/base/sql/01.kaltura_ce_tables.sql /opt/kaltura/app/deployment/base/sql/04.stored_procedures.sql"

kaltura_sphinx_log_PRIVILEGES="INSERT,UPDATE,DELETE,SELECT,ALTER,CREATE"
kaltura_sphinx_log_USER="kaltura"
kaltura_sphinx_log_SQL_FILES="/opt/kaltura/app/deployment/base/sql/01.kaltura_sphinx_ce_tables.sql"

kalturadw_PRIVILEGES="INSERT,UPDATE,DELETE,SELECT,EXECUTE"
kalturadw_USER="etl"
kalturadw_ds_PRIVILEGES="INSERT,UPDATE,DELETE,SELECT,EXECUTE"
kalturadw_ds_USER="etl"
kalturadw_bisources_PRIVILEGES="INSERT,UPDATE,DELETE,SELECT,EXECUTE"
kalturadw_bisources_USER="etl"
kalturalog_PRIVILEGES="INSERT,UPDATE,DELETE,SELECT,LOCK TABLES"
kalturalog_USER="etl"

USER_EXISTS=`echo "SELECT EXISTS(SELECT 1 FROM mysql.user WHERE user = 'kaltura');" |  mysql -h$MYSQL_HOST -u$MYSQL_SUPER_USER -p$MYSQL_SUPER_USER_PASSWD -P$MYSQL_PORT`
if [ "$USER_EXISTS" != "1" ];then
  echo "CREATE USER kaltura;"
  echo "CREATE USER kaltura@'%' IDENTIFIED BY '$DB1_PASS' ;"  | mysql -h$MYSQL_HOST -u$MYSQL_SUPER_USER -p$MYSQL_SUPER_USER_PASSWD -P$MYSQL_PORT
fi
USER_EXISTS=`echo "SELECT EXISTS(SELECT 1 FROM mysql.user WHERE user = 'etl');" |  mysql -h$MYSQL_HOST -u$MYSQL_SUPER_USER -p$MYSQL_SUPER_USER_PASSWD -P$MYSQL_PORT`
if [ "$USER_EXISTS" != "1" ];then
  echo "CREATE USER etl@'%';"
  echo "CREATE USER etl IDENTIFIED BY '$DWH_PASS' ;"  | mysql -h$MYSQL_HOST -u$MYSQL_SUPER_USER -p$MYSQL_SUPER_USER_PASSWD -P$MYSQL_PORT
fi

for DB in $OP_DBS $DWH_DBS;do
  echo "CREATE DATABASE $DB;"
  echo "CREATE DATABASE $DB;" | mysql -h$MYSQL_HOST -u$MYSQL_SUPER_USER -p$MYSQL_SUPER_USER_PASSWD -P$MYSQL_PORT
  PRIVS=${DB}_PRIVILEGES
  DB_USER=${DB}_USER
  # apply privileges:
  echo "GRANT ${!PRIVS} ON $DB.* TO '${!DB_USER}'@'%';FLUSH PRIVILEGES;" | mysql -h$MYSQL_HOST -u$MYSQL_SUPER_USER -p$MYSQL_SUPER_USER_PASSWD -P$MYSQL_PORT
  DB_SQL_FILES=${DB}_SQL_FILES
  # run table creation scripts:
  for SQL in ${!DB_SQL_FILES};do
    mysql -h$MYSQL_HOST -u$MYSQL_SUPER_USER -p$MYSQL_SUPER_USER_PASSWD -P$MYSQL_PORT $DB < $SQL
  done
done