<?php
/**
 * phpMyAdmin configuration file
 */

$i = 0;

/* Server: mysql.docker [1] */
$i++;
$cfg['Servers'][$i]['verbose'] = 'MySQL Service';
$cfg['Servers'][$i]['host'] = 'mysql.docker';
$cfg['Servers'][$i]['port'] = 3306;
$cfg['Servers'][$i]['socket'] = '';
$cfg['Servers'][$i]['connect_type'] = 'tcp';
$cfg['Servers'][$i]['auth_type'] = 'config';
$cfg['Servers'][$i]['user'] = 'app';
$cfg['Servers'][$i]['password'] = 'app';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = false;

/* End of servers configuration */

$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';

