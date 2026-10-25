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

if ($conn) {
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
}

if (!$group || !$members) {
    show_error_page("Team information unavailable", ["Team information is not available right now. Please try again later."]);
}

$page_title = "About the Team";
$page_description = "Meet the HD team behind Community Connect and how the project was built.";
$current_page = "about";
$page_style = '    .quote { font-family: "Source Serif 4", serif; font-style: italic; }';
include "header.inc";
?>

  <main id="main">
    <h1>About the Team</h1>

    <p class="intro"><?php echo e($group["intro"]); ?></p>

    <section>
      <h2>Group details</h2>
      <ul>
        <li>Group name: <strong style="letter-spacing: 0.03em;"><?php echo e($group["group_name"]); ?></strong>
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
        <dt><?php echo e($name); ?> - <span class="student-id"><?php echo e($m["student_id"]); ?></span></dt>
        <dd>
          <ul>
<?php foreach ($contributions[$m["member_id"]] ?? [] as $line) { ?>
            <li><?php echo e($line); ?></li>
<?php } ?>
          </ul>
        </dd>
<?php } ?>
      </dl>
    </section>

    <section>
      <h2>Quotes</h2>
      <p>Something each of us enjoys or feels strongly about.</p>
      <div class="grid">
<?php foreach ($members as $m) { ?>
        <blockquote class="box" lang="en">
          <p class="quote">"<?php echo e($m["quote_text"]); ?>" - <?php echo e($m["quote_by"]); ?></p>
          <footer>- <?php echo e($m["full_name"]); ?></footer>
        </blockquote>
<?php } ?>
      </div>
    </section>

    <section>
      <h2>The team</h2>
      <figure class="box team-photo">
        <img src="<?php echo e($group["photo_path"]); ?>"
             alt="<?php echo e($group["photo_alt"]); ?>"
             width="1000" height="600">
        <figcaption><?php echo e($group["photo_caption"]); ?></figcaption>
      </figure>
    </section>

    <section>
      <h2>Fun facts</h2>
      <table class="fun-facts">
        <caption>A few things you might not know about us</caption>
        <thead>
          <tr>
            <th scope="col">Name</th>
            <th scope="col">Fun fact</th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($members as $m) { ?>
          <tr>
            <td><?php echo e($m["full_name"]); ?><?php if ($m["nickname"]) { echo " (" . e($m["nickname"]) . ")"; } ?></td>
            <td><?php echo e($m["fun_fact"]); ?></td>
          </tr>
<?php } ?>
        </tbody>
      </table>
    </section>
  </main>

<?php include "footer.inc"; ?>
