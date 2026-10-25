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
$staff_link = ["stafflogin.php", "Staff Login", "stafflogin"];
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
<?php if (!$is_logged_in) { ?>
        <li class="nav-staff"><a href="<?php echo $staff_link[0]; ?>"<?php if (isset($current_page) && $current_page === $staff_link[2]) { echo ' aria-current="page"'; } ?>><?php echo $staff_link[1]; ?></a></li>
<?php } else { ?>
        <li class="nav-staff">
          <form method="post" action="logout.php">
            <input type="hidden" name="who" value="staff">
            <input type="hidden" name="token" value="<?php echo e($_SESSION["csrf_token"] ?? ""); ?>">
            <button type="submit">Log Out (<?php echo e($_SESSION["username"]); ?>)</button>
          </form>
        </li>
<?php } ?>
      </ul>
    </nav>
