<?php
define ('KEY_FILENAME',  0);
define ('KEY_EXTENSION', 1);

if (! is_readable ($dn = dirname(__FILE__) . '/' . ($subdn = 'avatars'))) {
    print 'Directory "' . $subdn . '" not found!';
    die;
}
$flfn = $dn . '/' . ($subflfn = '.filelist');
$reset = array_key_exists ('reset_filelist', $_POST) && $_POST['reset_filelist'] == 1;
$files = false;
if (is_readable ($flfn) || ! $reset) {
    $files = @unserialize (@file_get_contents ($flfn));
}
if (empty ($files) || $reset) {
    $dir = dir ($dn);
    $files = array ();
    $bad = array ('.', '..', $subflfn);
    $good = array ('png', 'jpg', 'gif');
    while (false !== ($f = $dir->read())) {
        if (in_array ($f, $bad)) continue;
        if (! in_array ($ext = preg_replace ('/^.*\.([a-z]{3,4})$/i', '$1', $f), $good)) continue;
        $files[] = array (KEY_FILENAME => $f, KEY_EXTENSION => $ext == 'jpg' ? 'jpeg' : $ext);
    }
    file_put_contents ($flfn, serialize ($files));
}
if ($reset) {
    header ('Location: ' . $_SERVER['PHP_SELF']);
    die;
}
if (empty ($files)) {
    print 'Directory "' . $subdn . '" is empty!';
    die;
}
shuffle ($files);
$entry = array_pop($files);

header ('Content-Transfer-Encoding: binary');
header ('Content-Type: image/' . $entry[KEY_EXTENSION]);
header ('Content-Length: ' . filesize($dn . '/' . $entry[KEY_FILENAME]));
ob_clean();
flush();
readfile ($dn . '/' . $entry[KEY_FILENAME]);

