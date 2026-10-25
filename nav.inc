<?php
/*
nav.inc: main navigation menu, included by header.inc.
Add a page by adding a line to $nav_links. The current page's link is highlighted.
Staff link: "Staff Login" for visitors; "Manage" and "Log Out" for logged-in managers.
*/

$is_logged_in = is_staff_logged_in();

// key = $current_page value => [file, link text]
$nav_links = [
    "index" => ["index.php", "Home"],
    "jobs"  => ["jobs.php",  "Jobs"],
    "apply" => ["apply.php", "Apply"],
    "about" => ["about.php", "About"],
];

if ($is_logged_in) {
    $nav_links["manage"] = ["manage.php", "Manage"];
}

// Staff link, styled separately (class "nav-staff"): [file, link text, $current_page value]
if ($is_logged_in) {
    $staff_link = ["logout.php?who=staff&amp;token=" . urlencode($_SESSION["csrf_token"] ?? ""), "Log Out (" . e($_SESSION["username"]) . ")", "logout"];
} else {
    $staff_link = ["stafflogin.php", "Staff Login", "stafflogin"];
}
?>
    <nav class="site-nav" aria-label="Main navigation">
      <details>
        <summary aria-label="Menu"></summary>
      </details>
      <ul>
<?php foreach ($nav_links as $key => $link) {
    $is_current = isset($current_page) && $current_page === $key;
?>
        <li><a href="<?php echo $link[0]; ?>"<?php if ($is_current) { echo ' aria-current="page"'; } ?>><?php echo $link[1]; ?></a></li>
<?php } ?>
        <li class="nav-staff"><a href="<?php echo $staff_link[0]; ?>"<?php if (isset($current_page) && $current_page === $staff_link[2]) { echo ' aria-current="page"'; } ?>><?php echo $staff_link[1]; ?></a></li>
      </ul>
    </nav>
