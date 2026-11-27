<?php
$currentPage = basename($_SERVER['PHP_SELF']);
if ($currentPage === 'search.php') {
    $currentPage = 'jobs.php';
}

// Link => label.
$navLinks = [
    'index.php'  => 'Home',
    'jobs.php'   => 'Jobs',
    'apply.php'  => 'Apply',
    'about.php' => 'About us',
];
?>
        <nav aria-label="Main">
            <ul>
<?php foreach ($navLinks as $href => $label): ?>
                <li><a href="<?php echo $href; ?>"<?php if ($href === $currentPage) echo ' aria-current="page"'; ?>><?php echo $label; ?></a></li>
<?php endforeach; ?>
            </ul>
        </nav>
