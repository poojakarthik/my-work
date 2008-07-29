<? include('header.php'); ?>

<?
if ($args[1] === 'view' && $args[2] === 'all') {
	include('ticket_view_all.php');
} elseif ($args[1] === 'view' && $args[2] === 'mine') {
	include('ticket_view_mine.php');
} elseif ($args[1] === 'view' && preg_match('/\d+/', $args[2])) {
	include('ticket_view.php');
} elseif ($args[1] === 'new') {
	include('ticket_new.php');
}
?>
<? include('footer.php'); ?>
