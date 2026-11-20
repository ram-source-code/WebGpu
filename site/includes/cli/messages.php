<?php
// Owner tool for contact messages (no web admin by design). Run from the site folder:
//   php includes/cli/messages.php list                 newest first
//   php includes/cli/messages.php show 12
//   php includes/cli/messages.php delete 12            one message
//   php includes/cli/messages.php forget a@b.com       every message from one person (privacy request)
//   php includes/cli/messages.php export > out.json    copy of all messages
//   php includes/cli/messages.php backup               consistent copy into data/backups/ (VACUUM INTO)
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASE', '');
$_SERVER['SCRIPT_NAME'] = '/index.php';
require dirname(__DIR__) . '/bootstrap.php';
restore_exception_handler();
while (ob_get_level()) ob_end_flush();

$db = open_db();
[$cmd, $arg] = [$argv[1] ?? 'list', $argv[2] ?? ''];
switch ($cmd) {
    case 'list':
        foreach ($db->query('SELECT id, created_at, name, email, mailed, substr(message, 1, 60) AS preview FROM messages ORDER BY id DESC') as $r) {
            printf("#%-4d %s  %-24s %-30s %s  %s\n", $r['id'], substr($r['created_at'], 0, 16), mb_strimwidth($r['name'], 0, 24), $r['email'], $r['mailed'] ? 'mailed' : '      ', str_replace("\n", ' ', $r['preview']));
        }
        break;
    case 'show':
        $s = $db->prepare('SELECT * FROM messages WHERE id = ?'); $s->execute([(int)$arg]);
        $r = $s->fetch() ?: exit("No message #$arg\n");
        echo "#{$r['id']}  {$r['created_at']}\n{$r['name']} <{$r['email']}>\n\n{$r['message']}\n";
        break;
    case 'delete':
        $s = $db->prepare('DELETE FROM messages WHERE id = ?'); $s->execute([(int)$arg]);
        echo $s->rowCount() ? "Deleted #$arg\n" : "No message #$arg\n";
        break;
    case 'forget':
        $s = $db->prepare('DELETE FROM messages WHERE lower(email) = lower(?)'); $s->execute([$arg]);
        echo 'Deleted ' . $s->rowCount() . " message(s) from that address\n";
        break;
    case 'export':
        echo json_encode($db->query('SELECT * FROM messages ORDER BY id')->fetchAll(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
        break;
    case 'backup':
        $dir = ROOT . '/data/backups';
        is_dir($dir) || mkdir($dir, 0700, true);
        $file = $dir . '/' . date('Ymd-His') . '.sqlite';
        $db->exec('VACUUM INTO ' . $db->quote($file));
        echo "Backup written: data/backups/" . basename($file) . "\n";
        break;
    default:
        echo "Commands: list | show ID | delete ID | forget EMAIL | export | backup\n";
}
