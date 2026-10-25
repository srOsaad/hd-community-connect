<?php
require_once "db_connect.inc";
require_once "functions.inc";
require_once "error.inc";

if (!$conn) {
    show_database_error();
}

$group = null;
$members = [];
$contributions = [];

$result = mysqli_query($conn, "SELECT * FROM group_info LIMIT 1");
if ($result) {
    $group = mysqli_fetch_assoc($result);
}

$result = mysqli_query($conn, "SELECT * FROM members ORDER BY sort_order");
if ($result) {
    $members = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$result = mysqli_query($conn,
    "SELECT member_id, project_part, description FROM contributions
     ORDER BY member_id, project_part, contribution_id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $contributions[$row["member_id"]][] = "Part " . $row["project_part"] . ": " . $row["description"];
    }
}

$quotes = array_filter($members, function ($m) {
    return trim((string) $m["quote_text"]) !== "";
});
$facts = array_filter($members, function ($m) {
    return trim((string) $m["fun_fact"]) !== "";
});

if (!$group || !$members) {
    show_error_page("Team information unavailable", ["Team information is not available right now. Please try again later."]);
}

$page_title = "About the Team";
$page_description = "Meet the HD team behind Community Connect and how the project was built.";
$current_page = "about";
$page_style = '    .quote { font-family: var(--font-heading); font-style: italic; }
    blockquote { margin: 0; }
    dd { margin-left: 0; }
    figure { margin: 0; }
    .group-name { letter-spacing: 0.03em; }';
include "header.inc";
?>

  <main id="main">
    <h1>About the Team</h1>

    <p class="intro"><?php echo e($group["intro"]); ?></p>

    <section>
      <h2>Group details</h2>
      <ul>
        <li>Group name: <strong class="group-name"><?php echo e($group["group_name"]); ?></strong>
          <ul>
            <li>Lecture: <?php echo e($group["lecture_time"]); ?> (<?php echo e($group["lecture_staff"]); ?>)</li>
            <li>Tutorial: <?php echo e($group["tutorial_time"]); ?> (<?php echo e($group["tutorial_staff"]); ?>)</li>
          </ul>
        </li>
      </ul>
    </section>

    <section class="box">
      <h2>Member contributions</h2>
      <dl>
<?php foreach ($members as $m) {
    $name = $m["full_name"];
    if ($m["nickname"]) {
        $name = $name . " (" . $m["nickname"] . ")";
    }
?>
        <dt><?php echo e($name); ?> - <?php echo e($m["student_id"]); ?></dt>
        <dd>
<?php if (empty($contributions[$m["member_id"]])) { ?>
          <p>No contributions recorded yet.</p>
<?php } else { ?>
          <ul>
<?php foreach ($contributions[$m["member_id"]] as $line) { ?>
            <li><?php echo e($line); ?></li>
<?php } ?>
          </ul>
<?php } ?>
        </dd>
<?php } ?>
      </dl>
    </section>

<?php if ($quotes) { ?>
    <section>
      <h2>Quotes</h2>
      <p>Something each of us enjoys or feels strongly about.</p>
      <div class="grid">
<?php foreach ($quotes as $m) { ?>
        <blockquote class="box" lang="en">
          <p class="quote">"<?php echo e($m["quote_text"]); ?>"</p>
          <footer><?php if (trim((string) $m["quote_by"]) !== "") { ?><cite><?php echo e($m["quote_by"]); ?></cite>, chosen<?php } else { ?>Chosen<?php } ?> by <?php echo e($m["full_name"]); ?></footer>
        </blockquote>
<?php } ?>
      </div>
    </section>
<?php } ?>

    <section>
      <h2>The team</h2>
      <figure class="box">
        <img src="<?php echo e($group["photo_path"]); ?>"
             alt="<?php echo e($group["photo_alt"]); ?>"
             width="1200" height="900">
        <figcaption><?php echo e($group["photo_caption"]); ?></figcaption>
      </figure>
    </section>

<?php if ($facts) { ?>
    <section>
      <h2>Fun facts</h2>
      <table>
        <caption>A few things you might not know about us</caption>
        <thead>
          <tr>
            <th scope="col">Name</th>
            <th scope="col">Fun fact</th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($facts as $m) { ?>
          <tr>
            <td><?php echo e($m["full_name"]); ?><?php if ($m["nickname"]) { echo " (" . e($m["nickname"]) . ")"; } ?></td>
            <td><?php echo e($m["fun_fact"]); ?></td>
          </tr>
<?php } ?>
        </tbody>
      </table>
    </section>
<?php } ?>
  </main>

<?php include "footer.inc"; ?>
