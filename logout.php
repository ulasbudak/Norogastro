<?php
/**
 * Kullanıcı çıkış endpoint'i
 */

require_once 'session.php';

logout();

header('Location: index.html');
exit;



