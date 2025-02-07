<?php

function toptext() {
    return 'Configure your eyeOS';
}

function getContent() {
    if (isset($_POST['mysqlhost'])) {
        $link = mysql_connect($_POST['mysqlhost'], $_POST['mysqluser'], $_POST['mysqlpass']);
        if (!$link) {
            echo '<p>Unable to connect to database: ' . mysql_error() . '</p>';
            echo '<p><a href="index.php?step=configuration">Click here to go back</a></p>';
            return;
        }

        if (!mysql_select_db($_POST['mysqldb'], $link)) {
            echo '<p>Unable to select database: ' . mysql_error() . '</p>';
            echo '<p><a href="index.php?step=configuration">Click here to go back</a></p>';
            return;
        }

        set_time_limit(0);
        $files = array(
            '../eyeos/extras/EyeosUMSQL/EyeosUMSQL.sql',
            '../eyeos/extras/EyeosTagsSQL/EyeosTags.sql',
            '../eyeos/extras/EyeosMetaSQL/EyeosMetaSQL.sql',
            '../eyeos/extras/EyeosPeopleSQL/EyeosPeopleSQL.sql',
            '../eyeos/extras/EyeosPeopleSQL/EyeosPeopleUpdateSQL.sql',
            '../eyeos/extras/PresenceSQL/Presence.sql',
            '../eyeos/extras/rMailApplicationSQL/rMailApplication.sql',
            '../eyeos/extras/CalendarSQL/Calendar.sql',
            '../eyeos/extras/GroupCalendarSQL/GroupCalendar.sql',
            '../eyeos/extras/EyeosEventsNotificationSQL/EyeosEventNotification.sql',
            '../eyeos/extras/LanguageAdminSQL/languageAdmin.sql',
            '../eyeos/extras/netSyncSQL/netSync.sql',
            '../eyeos/extras/UrlShareSQL/UrlShareSQL.sql'
        );

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $content = explode("\n", $content);
            $buffer = "";
            foreach ($content as $line) {
                $line = str_replace("\r", "", $line);
                $line = trim($line);
                $buffer .= $line . "\r\n";
                if (substr($line, -1, 1) == ';') {
                    mysql_query($buffer, $link);
                    $buffer = "";
                }
            }
        }

        $rootpass = sha1($_POST['eyerootpass'] . sha1($_POST['eyerootpass']));

        $sql = 'UPDATE eyeosuser set password = \'' . $rootpass . '\' where id = \'eyeID_EyeosUser_root\'';

        mysql_query($sql, $link);

        $settingstext = getSettingsText($_POST['mysqlhost'], $_POST['mysqldb'], $_POST['mysqluser'], $_POST['mysqlpass']);

        file_put_contents('../settings.php', $settingstext);

        header('Location: index.php?step=end');
    } else {
        echo '<center><h2 class="bigtitle">eyeOS 2 configuration</h2></center>';
        ?>
        <script>
            function checkandsend() {
                if (document.getElementById('eyerootpass').value != "") {
                    document.getElementById('forminfo').submit();
                    document.getElementById('configcontent').innerHTML = '<center><p>Installing eyeOS...</p><img style="margin-top:40px" src="ajax-loader.gif" /></center>';
                } else {
                    alert('eyeOS root password cannot be empty');
                }
            }
        </script>
        <div id="configcontent">
            <center>Database configuration</center>
            <form id="forminfo" action="index.php?step=configuration" method="post">
                <table style="margin-top:20px;width:600px;">
                    <tr>
                        <td style="padding-right:10px;" align="right">MySQL Host:</td>
                        <td><input name="mysqlhost" type="text" class="box" value="localhost" /></td>
                    </tr>
                    <tr>
                        <td style="padding-right:10px;" align="right">MySQL Database:</td>
                        <td><input name="mysqldb" type="text" class="box" value="eyeos" /></td>
                    </tr>
                    <tr>
                        <td style="padding-right:10px;" align="right">MySQL Username:</td>
                        <td><input name="mysqluser" type="text" class="box" value="" /></td>
                    </tr>
                    <tr>
                        <td style="padding-right:10px;" align="right">MySQL Password:</td>
                        <td><input name="mysqlpass" type="password" class="box" value="" /></td>
                    </tr>
                </table>
                <br />
                <center>EyeOS configuration</center>
                <table style="margin-top:20px;width:600px;">
                    <tr>
                        <td style="padding-right:10px;" align="right">eyeOS root password:</td>
                        <td><input id="eyerootpass" name="eyerootpass" type="text" class="box" value="" /></td>
                    </tr>
                </table>
                <br />
                <p id="sendbtn">
                    <center>
                        <a href="javascript:checkandsend();">
                            <div><img src="next.png" border="0" /></div>
                            <div style="margin-top:20px;">Continue with the installation</div>
                        </a>
                    </center>
                </p>
            </form>
        </div>
        <?php
    }
}

function getSettingsText($mysqlhost, $mysqldb, $mysqluser, $mysqlpass) {
    return "<?php
define('REAL_EYE_ROOT', 'eyeos');
define('EYE_ROOT', '.');
define('EYE_VERSION', '2.5');

define('BOOT_DIR', 'bootstrap');
define('SYSTEM_DIR', 'system');
define('SYSTEM_CONF_DIR', 'conf');
define('SYSTEM_CONF_PATH', EYE_ROOT . '/' . SYSTEM_DIR . '/' . SYSTEM_CONF_DIR);
define('SYSTEM_SKEL_DIR', 'skel');
define('SYSTEM_SKEL_PATH', SYSTEM_CONF_PATH . '/' . SYSTEM_SKEL_DIR);
define('KERNEL_DIR', 'kernel');
define('SERVICES_DIR', 'services');
define('LIBRARIES_DIR', 'libs');
define('FRAMEWORKS_DIR', 'Frameworks');
define('IMPLEMENTATIONS_DIR', 'implementations');
define('EXTERN_DIR', 'extern');
define('APPS_DIR', 'apps');
define('USERS_DIR', 'users');
define('USERS_PATH', EYE_ROOT . '/' . USERS_DIR);
define('USERS_CONF_DIR', 'conf');
define('USERS_FILES_DIR', 'files');
define('USERS_METAFILES_DIR', 'metafiles');
define('USERS_SHARE_DIR', 'share');
define('USERS_META_DIR', 'meta');
define('USERS_META_SETTINGS_FILENAME', 'settings.xml');
define('WORKGROUPS_DIR', 'workgroups');
define('WORKGROUPS_PATH', EYE_ROOT . '/' . WORKGROUPS_DIR);
define('WORKGROUPS_CONF_DIR', 'conf');
define('WORKGROUPS_FILES_DIR', 'files');
define('WORKGROUPS_METAFILES_DIR', 'metafiles');
define('WORKGROUPS_META_DIR', 'meta');
define('WORKGROUPS_META_SETTINGS_FILENAME', 'settings.xml');

define('LIBRARIES_PATH', EYE_ROOT . '/' . SYSTEM_DIR . '/' . KERNEL_DIR . '/' . LIBRARIES_DIR);
define('LIB_ABSTRACTION_DIR', 'abstraction');
define('LIB_ABSTRACTION_PATH', LIBRARIES_PATH . '/' . LIB_ABSTRACTION_DIR);
define('LIB_EXCEPTIONS_DIR', 'exceptions');
define('LIB_EXCEPTIONS_PATH', LIBRARIES_PATH . '/' . LIB_EXCEPTIONS_DIR);
define('LIB_EXCEPTIONS_SUBCLASSES_DIR', 'subclasses');
define('LIB_EXCEPTIONS_SUBCLASSES_PATH', LIB_EXCEPTIONS_PATH . '/' . LIB_EXCEPTIONS_SUBCLASSES_DIR);
define('LIB_EXCEPTIONS_USE_REALPATH', false);
define('LIB_UTF8_DIR', 'utf8');
define('LIB_UTF8_PATH', LIBRARIES_PATH . '/' . LIB_UTF8_DIR);
define('LIB_IDGEN_DIR', 'idGen');
define('LIB_IDGEN_PATH', LIBRARIES_PATH . '/' . LIB_IDGEN_DIR);
define('LIB_IDGEN_CONFIGURATION_PATH', SYSTEM_CONF_PATH . '/' . LIBRARIES_DIR . '/' . LIB_IDGEN_DIR);
define('LIB_IDGEN_SEMAPHORE_KEY', 20090914);
define('LIB_LOG4PHP_DIR', 'log4php');
define('LIB_LOG4PHP_PATH', LIBRARIES_PATH . '/' . LIB_LOG4PHP_DIR);
define('LIB_LOG4PHP_CONFIGFILE_PATH', SYSTEM_CONF_PATH . '/' . LIBRARIES_DIR . '/' . LIB_LOG4PHP_DIR . '/configuration.xml');
define('LIB_STREAMS_DIR', 'streams');
define('LIB_STREAMS_PATH', LIBRARIES_PATH . '/' . LIB_STREAMS_DIR);
define('LIB_UTILS_DIR', 'utils');
define('LIB_UTILS_PATH', LIBRARIES_PATH . '/' . LIB_UTILS_DIR);
define('LIB_OFFICE_SCREEN', 1);
define('LIB_OFFICE_SEPARATOR_ARG', '\'');
define('LIB_OFFICE_COMMAND', 'soffice');
define('LIB_OFFICE_CONVERSION', 'uno');

define('SERVICES_PATH', EYE_ROOT . '/' . SYSTEM_DIR . '/' . KERNEL_DIR . '/' . SERVICES_DIR);
define('SERVICE_FILESYSTEM_DIR', 'FileSystem');
define('SERVICE_FILESYSTEM_PATH', SERVICES_PATH . '/' . SERVICE_FILESYSTEM_DIR);
define('SERVICE_FILESYSTEM_LIBRARIES_DIR', 'libs');
define('SERVICE_FILESYSTEM_LIBRARIES_PATH', SERVICE_FILESYSTEM_PATH . '/' . IMPLEMENTATIONS_DIR . '/' . SERVICE_FILESYSTEM_LIBRARIES_DIR);
define('SERVICE_FILESYSTEM_CONFIGURATION_PATH', SYSTEM_CONF_PATH . '/' . KERNEL_DIR . '/' . SERVICES_DIR . '/' . SERVICE_FILESYSTEM_DIR);
define('SERVICE_FILESYSTEM_META_FILENAME', 'metadata.xml');
define('SERVICE_FILESYSTEM_META_USERFILENAME', 'user_metadata.xml');

define('SERVICE_USERFILESYSTEM_DIR', 'userFileSystem');
define('SERVICE_USERFILESYSTEM_PATH', SERVICES_PATH . '/' . SERVICE_USERFILESYSTEM_DIR);
define('SERVICE_USERFILESYSTEM_LIBRARIES_DIR', 'libs');
define('SERVICE_USERFILESYSTEM_LIBRARIES_PATH', SERVICE_USERFILESYSTEM_PATH . '/' . IMPLEMENTATIONS_DIR . '/' . SERVICE_USERFILESYSTEM_LIBRARIES_DIR);
define('SERVICE_USERFILESYSTEM_CONFIGURATION_PATH', SYSTEM_CONF_PATH . '/' . KERNEL_DIR . '/' . SERVICES_DIR . '/' . SERVICE_USERFILESYSTEM_DIR);
define('SERVICE_USERFILESYSTEM_META_FILENAME', 'metadata.xml');
define('SERVICE_USERFILESYSTEM_META_USERFILENAME', 'user_metadata.xml');

define('SERVICE_HTTPDIR', 'http');
define('SERVICE_HTTP_PATH', SERVICES_PATH . '/' . SERVICE_HTTPDIR);
define('SERVICE_HTTP_META_FILENAME', 'metadata.xml');
define('SERVICE_HTTP_META_USERFILENAME', 'user_metadata.xml');

define('MYSQL_USER', '$mysqluser');
define('MYSQL_PASS', '$mysqlpass');
define('MYSQL_HOST', '$mysqlhost');
define('MYSQL_DB', '$mysqldb');
define('MYSQL_PORT', 3306);
define('MYSQL_SOCKET', false);

define('EYE_WEBPATH', '/');
define('EYE_WEBPORT', 80);
define('EYE_WEBSSLPORT', 443);
define('EYE_WEBSSL', false);
define('EYE_WEEB_SERVER', 'apache');

define('SESSION_AUTH', 'sessionAuth');
define('SESSION_AUTH_USER', 'sessionAuthUser');
define('SESSION_AUTH_ADMIN', 'sessionAuthAdmin');
define('SESSION_WORKGROUP', 'sessionWorkgroup');

?>";
}
?>
