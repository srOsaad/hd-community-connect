<?php
/*
Using header.inc (with nav.inc inside) and footer.inc.
Page content is unchanged from Part 1 apart from links (.html to .php).
*/

$page_title = "Home";
$page_description = "Community Connect is a not-for-profit supporting community programs, volunteer registration, events, donations and access to support resources.";
$current_page = "index";

// Embedded CSS for this page
$page_style = <<<'CSS'
    /* Embedded CSS */

    .hero {
      position: relative;
      padding: clamp(1.75rem, 5vw, 3rem);
      background-image: linear-gradient(135deg, var(--surface) 0%, var(--brand-light) 100%);
    }

    .hero .eyebrow {
      margin: 0 0 0.4em;
      color: var(--ink-muted);
      font-size: var(--step--1);
      font-weight: 600;
      font-style: italic;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    /* Round tinted badge behind each focus area icon */
    .focus-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 3rem;
      height: 3rem;
      margin-bottom: 0.75rem;
      border-radius: 50%;
      background-color: var(--brand-light);
      color: var(--brand);
      font-family: var(--font-heading);
      font-size: var(--step-1);
      font-weight: 700;
    }

    /* Acknowledgement of Country and the Community quote: plain text, no
       panel or accent border. Also let their paragraphs use the full
       column width instead of the 65ch cap on main p/li (and, for the
       blockquote, its own default side margins), so neither wraps more
       than it needs to. */
    .acknowledgement p,
    blockquote p {
      max-width: none;
    }

    blockquote {
      margin: 0 0 0.75rem;
    }

    ul.grid {
      list-style: none;
      padding: 0;
    }
CSS;

include "header.inc";
?>

  <main id="main">

    <section class="hero box">
      <p class="eyebrow">Not-for-profit organisation</p>
      <h1>Community Connect</h1>
      <p class="intro">Bringing people and support closer together.</p>
      <p>Community Connect is a not-for-profit organisation improving its digital
        services to support community programs, volunteer registration, event
        information, donations and access to support resources.</p>
      <div class="group">
        <a class="btn" href="jobs.php">View Job Openings</a>
        <a class="btn-secondary" href="about.php">Learn About Us</a>
      </div>
    </section>

    <section aria-labelledby="about-community-heading">
      <h2 id="about-community-heading">What Is a Community?</h2>
      <blockquote cite="https://en.wikipedia.org/wiki/Community">
        <p>A community is a social unit (a group of people) with one or more
          shared socially-significant characteristics, being place, set of
          norms, culture, religion, values, customs, or identity. Communities
          may share a sense of place situated in a given geographical area
          (e.g. a country, village, town, or neighborhood) or in virtual
          space through communication platforms. Durable good relations that
          extend beyond immediate genealogical ties also define a sense of
          community, important to people's identity, practice, and roles in
          social institutions such as family, home, work, government,
          society, or humanity at large.</p>
      </blockquote>
      <p class="hint">Source: <a href="https://en.wikipedia.org/wiki/Community"
           target="_blank" rel="noopener noreferrer">"Community"<span
           class="visually-hidden"> (opens in a new tab)</span></a>, Wikipedia,
        licensed under <a href="https://creativecommons.org/licenses/by-sa/4.0/"
           target="_blank" rel="noopener noreferrer">CC BY-SA 4.0<span
           class="visually-hidden"> (opens in a new tab)</span></a>.</p>
    </section>

    <section aria-labelledby="focus-heading">
      <h2 id="focus-heading">What We Help With</h2>
      <ul class="grid">
        <li class="box">
          <span class="focus-icon" aria-hidden="true">C</span>
          <h3>Community Programs</h3>
          <p>Coordinating local initiatives that connect people with the
            services and support their community offers.</p>
        </li>
        <li class="box">
          <span class="focus-icon" aria-hidden="true">V</span>
          <h3>Volunteer Registration</h3>
          <p>A simple sign-up process so volunteers can find opportunities
            that match their time and skills.</p>
        </li>
        <li class="box">
          <span class="focus-icon" aria-hidden="true">E</span>
          <h3>Event Information</h3>
          <p>Up-to-date details on community events, so nobody misses a
            chance to get involved.</p>
        </li>
        <li class="box">
          <span class="focus-icon" aria-hidden="true">D</span>
          <h3>Donations &amp; Resources</h3>
          <p>Secure ways to give, alongside a directory of support
            resources for people who need them.</p>
        </li>
      </ul>
    </section>

    <section aria-labelledby="programs-heading">
      <h2 id="programs-heading">Support Programs at a Glance</h2>
      <div class="table-wrap">
        <table>
          <caption>Overview of Community Connect's core programs</caption>
          <thead>
            <tr>
              <th scope="col">Program</th>
              <th scope="col">Focus</th>
              <th scope="col">Delivery</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row" rowspan="2">Community Programs</th>
              <td>Volunteer registration</td>
              <td>Online &amp; in-person</td>
            </tr>
            <tr>
              <td>Event coordination</td>
              <td>Online &amp; in-person</td>
            </tr>
            <tr>
              <th scope="row">Donations</th>
              <td colspan="2">Secure online giving portal</td>
            </tr>
            <tr>
              <th scope="row">Support Resources</th>
              <td colspan="2">Directory of local community services</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="acknowledgement" aria-labelledby="acknowledgement-heading">
      <h2 id="acknowledgement-heading">Acknowledgement of Country</h2>
      <p>Community Connect acknowledges the Traditional Owners of the lands on
        which we work, connect and support our community, and recognises
        their continuing connection to land, waters and culture. We pay our
        respects to Elders past and present, and extend that respect to all
        Aboriginal and Torres Strait Islander peoples engaging with our
        services today.</p>
    </section>

  </main>

<?php include "footer.inc"; ?>