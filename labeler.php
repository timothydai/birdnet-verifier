<?php

session_start();
if (!isset($_SESSION["username"])) {
  header("Location: index.php");
  exit;
}

$connect = mysqli_connect("159.89.149.97", "birdnetv_public", "birdnetrools!", "birdnetv_base", "3306");
$location = $_GET["location"];
$sample_idx = $_GET["sample"];

if (str_contains($location, "all")) {
  if (str_contains($location, "cam")) {
    $sample = mysqli_query($connect, "SELECT * FROM birdnet_detections WHERE recording_location LIKE 'Cam%' LIMIT 1 OFFSET $sample_idx;")->fetch_assoc();
    $number_of_samples = mysqli_query($connect, "SELECT COUNT(*) as num_recs FROM birdnet_detections WHERE recording_location LIKE 'Cam%';")->fetch_assoc()["num_recs"];
  } else {
    $sample = mysqli_query($connect, "SELECT * FROM birdnet_detections WHERE recording_location NOT LIKE 'Cam%' LIMIT 1 OFFSET $sample_idx;")->fetch_assoc();
    $number_of_samples = mysqli_query($connect, "SELECT COUNT(*) as num_recs FROM birdnet_detections WHERE recording_location NOT LIKE 'Cam%';")->fetch_assoc()["num_recs"];
  }
} else {
  $sample = mysqli_query($connect, "SELECT * FROM birdnet_detections WHERE recording_location = '$location' LIMIT 1 OFFSET $sample_idx;")->fetch_assoc();
  $number_of_samples = mysqli_query($connect, "SELECT COUNT(*) as num_recs FROM birdnet_detections WHERE recording_location = '$location';")->fetch_assoc()["num_recs"];
}

if (isset($_POST["submit"])) {
  date_default_timezone_set("America/Los_Angeles");
  // Connecting to sql db.
  $connect = mysqli_connect("159.89.149.97", "birdnetv_timdai", "b1rdn3tr00l5", "birdnetv_base", "3306");
  $query_format = "INSERT INTO expert_ids VALUES %s;";
  $values = "";
  $value_format = '(%d, %d, "%s", "%s", "%s", "%s", %d),';

  // Sending form data to sql db.
  $now = date("Y-m-d H:i:s");

  foreach (explode("\n", $_POST["selected_species"]) as $common_name) {
    if (substr($common_name, -1) === "\r") {
      $common_name = substr($common_name, 0, -1);
    }
    $value = sprintf(
      $value_format,
      0,
      $sample["id"],
      $common_name,
      $_POST["comments"],
      $_SESSION["username"],
      $now,
      0
    );
    $values .= $value;
  }
  foreach (explode("\n", $_POST["unlisted_selected_species"]) as $common_name) {
    if ($common_name !== "") {
      if (substr($common_name, -1) === "\r") {
        $common_name = substr($common_name, 0, -1);
      }
      $value = sprintf(
        $value_format,
        0,
        $sample["id"],
        $common_name,
        $_POST["comments"],
        $_SESSION["username"],
        $now,
        1
      );
      $values .= $value;
    }
  }
  $values = substr($values, 0, -1);
  if ($_SESSION["username"] !== "Test") {
    mysqli_query($connect, sprintf($query_format, $values));
  }

  header("Location: labeler.php?location=" . $_GET["location"] . "&sample=" . ($_GET["sample"] + 1));
  exit;
}

if ($_GET["sample"] >= $number_of_samples) {
  header("Location: done.php");
  exit;
}

$birdnet_detection_id = $sample["id"];
$last_submission = mysqli_query($connect, "SELECT * FROM expert_ids WHERE birdnet_detection_id='$birdnet_detection_id' ORDER BY logged_date DESC LIMIT 1;")->fetch_assoc();
if ($last_submission !== null) {
  // Set last updated string.
  $logged_date = $last_submission["logged_date"];
  $logged_user = $last_submission["logged_user"];
  $last_updated_str = $logged_user . ", on " . $logged_date;

  // Gather identifications from last submission.
  $last_ids = mysqli_query($connect, "SELECT * FROM expert_ids WHERE birdnet_detection_id='$birdnet_detection_id' AND logged_date='$logged_date';");
  $last_listed_species = array();
  $last_unlisted_species = array();
  while ($row = $last_ids->fetch_assoc()) {
    if ($row["unlisted"] === "1") {
      $last_unlisted_species[] = $row["species_common_name"];
    } else {
      $last_listed_species[] = $row["species_common_name"];
    }
  }

  // Pre-populate species identifications.
  $last_listed_species_textarea = implode("&#13;&#10", $last_listed_species);
  $last_unlisted_species_textarea = implode("&#13;&#10", $last_unlisted_species);
  $last_unlisted_species_input = implode(",", $last_unlisted_species);
  $last_comment = $last_submission["comments"];

  // Get number of agreeing labelers.
  $last_submission_arr = mysqli_query($connect, "SELECT species_common_name, comments FROM expert_ids WHERE birdnet_detection_id='$birdnet_detection_id' AND logged_date='$logged_date' ORDER BY species_common_name;")->fetch_all();

  $dist_submissions = mysqli_query($connect, "SELECT DISTINCT logged_user, logged_date FROM expert_ids WHERE birdnet_detection_id='$birdnet_detection_id' ORDER BY logged_date DESC;")->fetch_all();

  $number_of_agreements = 1;
  $agreers = array();
  $agreers[] = $logged_user;
  $seen_labelers = array();
  foreach ($dist_submissions as $dist_submission) {
    $cur_subm_user = $dist_submission[0];
    $cur_subm_date = $dist_submission[1];

    // Only assess the user's most recent submission.
    if (in_array($cur_subm_user, $seen_labelers)) {
      continue;
    }
    $seen_labelers[] = $cur_subm_user;

    if (in_array($cur_subm_user, $agreers)) {
      continue;
    }

    $ids_cur_subm = mysqli_query($connect, "SELECT species_common_name, comments FROM expert_ids WHERE birdnet_detection_id='$birdnet_detection_id' AND logged_user='$cur_subm_user' AND logged_date='$cur_subm_date' ORDER BY species_common_name;")->fetch_all();
    if ($ids_cur_subm === $last_submission_arr) {
      $number_of_agreements += 1;
      $agreers[] = $cur_subm_user;
    }
  }
  $agreers_message = implode("; ", $agreers);
} else {
  $last_updated_str = "";
  $last_listed_species_textarea = "";
  $last_unlisted_species_textarea = "";
  $last_unlisted_species_input = "";
  $last_comment = "";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Labeler</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="scripts/filter_species.js"></script>
  <script src="scripts/toggle_species.js"></script>
  <style>
    table,
    tr,
    th,
    td {
      border: 1px solid black;
      border-collapse: collapse;
      padding: 3px;
      font-weight: normal;
      table-layout: fixed;
    }

    table {
      width: 100%;
    }

    th {
      width: 80px;
    }

    #spectrogram_info_bubble {
      cursor: default;
    }

    #spectrogram_info_bubble #spectrogram_info_text {
      visibility: hidden;
    }

    #spectrogram_info_bubble:hover #spectrogram_info_text {
      visibility: visible;
    }
  </style>
</head>

<body style="min-width:600px;display:flex;justify-content:center;">
  <div style="min-width:600px;width:600px;">
    <!-- Test user warning -->
    <?php if ($_SESSION["username"] === "Test") { ?>
      <div style="color: red;">Warning: You are labeling as a Test user. Your submissions will not be recorded.</div>
    <?php } ?>

    <!-- Record count -->
    <div style="margin-bottom: 10px">Record <?php echo $sample_idx + 1; ?> of <?php echo $number_of_samples; ?></div>

    <!-- Information -->
    <div>
      <?php if ($last_submission !== null) { ?>
        <div style="margin-bottom:15px;">
          <!-- Sticker -->
          <span style="background-color:rgb(100,100,255);color:white;border-radius:7px;padding:3px;padding-left:5px;padding-right:5px;">
            <?php echo $number_of_agreements; ?>
            <?php if ($number_of_agreements === 1) { ?>
              person (<?php echo $agreers_message; ?>) thinks
            <?php } else { ?>
              people (<?php echo $agreers_message; ?>) think
            <?php } ?>
            this label is correct.
          </span>
          <div style="margin-top:5px;">
            If you agree, click "Submit" to record your agreement. If you disagree, edit the field(s) below and click "Submit". If you are unsure, click "Skip".
          </div>
        </div>
      <?php } ?>

      <!-- Data -->
      <div style="display:flex;">
        <!-- Audio and audio metadata table -->
        <div style="width:50%; margin-bottom:30px;">
          <!-- Audio -->
          <div>
            <audio controls src=<?php echo $sample["audio_uri"]; ?> style="margin-bottom: 15px; "></audio>
          </div>
          <!-- Audio metadata table -->
          <div>
            <table>
              <tr>
                <th>Location</th>
                <td><?php echo $sample["recording_location"]; ?></td>
              </tr>
              <tr>
                <th>Filename</th>
                <td style="overflow-wrap: break-word;"><?php echo $sample["filename"]; ?></td>
              </tr>
              <tr>
                <th>Datetime</th>
                <td><?php echo $sample["recording_datetime"]; ?></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Spectrogram -->
        <div style="width:50%;text-align:right;display:flex;flex-direction:column;">
          <!-- Spectrogram info bubble -->
          <div style="margin-bottom:-40px;z-index:1;position:relative;">
            <span id="spectrogram_info_bubble">&#9432;
              <span id="spectrogram_info_text" style="position:absolute;left:102%;width:150px;text-align:left;background-color:rgb(210,210,210);padding:5px;border-radius:5px;">
                A spectrogram showing time in seconds (x), frequency in Hz (y), and amplitude in dB (color). Each vertical slice is a moment in time, with
                louder frequencies marked with a brighter color.
              </span>
            </span>
          </div>
          <!-- Spectrogram image -->
          <div>
            <img src=<?php echo $sample["spec_uri"] ?> style="width:100%;z-index:-1;">
          </div>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div style="margin-bottom: 15px">
      <form action="" method="post">
        <!-- Species selection header -->
        <div style="margin-bottom: 5px;">
          <div style="font-weight:bold;">Select species (Common Name):</div>
          <div style="font-style:italic;">Leave all selections blank if no bird species present.</div>
        </div>

        <!-- Species selector -->
        <div style="margin-bottom: 15px;display:flex;justify-content:space-between">
          <!-- Species selection half -->
          <div style="width:47%">
            <div style="margin-bottom: 5px;">
              Click on a species name once to select it and again to deselect it.
            </div>
            <!-- Species list filter -->
            <input type="text" style="width:100%;margin-bottom:5px;" id="filterMultipleSelection" placeholder="Filter species names here" autocomplete="off" />
            <!-- Species list selector -->
            <select id="species_common_name" name="species_common_name" style="width:103%;height:150px;" multiple>
              <option value="Acorn Woodpecker">Acorn Woodpecker</option>
              <option value="Allen's Hummingbird">Allen's Hummingbird</option>
              <option value="American Bittern">American Bittern</option>
              <option value="American Coot">American Coot</option>
              <option value="American Crow">American Crow</option>
              <option value="American Goldfinch">American Goldfinch</option>
              <option value="American Kestrel">American Kestrel</option>
              <option value="American Robin">American Robin</option>
              <option value="American Wigeon">American Wigeon</option>
              <option value="Anna's Hummingbird">Anna's Hummingbird</option>
              <option value="Ash-throated Flycatcher">Ash-throated Flycatcher</option>
              <option value="Bald Eagle">Bald Eagle</option>
              <option value="Baltimore Oriole">Baltimore Oriole</option>
              <option value="Band-tailed Pigeon">Band-tailed Pigeon</option>
              <option value="Bank Swallow">Bank Swallow</option>
              <option value="Barn Owl">Barn Owl</option>
              <option value="Barn Swallow">Barn Swallow</option>
              <option value="Belted Kingfisher">Belted Kingfisher</option>
              <option value="Bewick's Wren">Bewick's Wren</option>
              <option value="Black Phoebe">Black Phoebe</option>
              <option value="Black-crowned Night-Heron">Black-crowned Night-Heron</option>
              <option value="Black-headed Grosbeak">Black-headed Grosbeak</option>
              <option value="Black-throated Gray Warbler">Black-throated Gray Warbler</option>
              <option value="Blue-gray Gnatcatcher">Blue-gray Gnatcatcher</option>
              <option value="Brewer's Blackbird">Brewer's Blackbird</option>
              <option value="Broad-winged Hawk">Broad-winged Hawk</option>
              <option value="Brown Creeper">Brown Creeper</option>
              <option value="Brown Pelican">Brown Pelican</option>
              <option value="Brown-headed Cowbird">Brown-headed Cowbird</option>
              <option value="Bufflehead">Bufflehead</option>
              <option value="Bullock's Oriole">Bullock's Oriole</option>
              <option value="Bushtit">Bushtit</option>
              <option value="California Gull">California Gull</option>
              <option value="California Quail">California Quail</option>
              <option value="California Scrub-Jay">California Scrub-Jay</option>
              <option value="California Thrasher">California Thrasher</option>
              <option value="California Towhee">California Towhee</option>
              <option value="Canada Goose">Canada Goose</option>
              <option value="Canvasback">Canvasback</option>
              <option value="Caspian Tern">Caspian Tern</option>
              <option value="Cassin's Vireo">Cassin's Vireo</option>
              <option value="Cedar Waxwing">Cedar Waxwing</option>
              <option value="Chestnut-backed Chickadee">Chestnut-backed Chickadee</option>
              <option value="Chipping Sparrow">Chipping Sparrow</option>
              <option value="Cinnamon Teal">Cinnamon Teal</option>
              <option value="Cliff Swallow">Cliff Swallow</option>
              <option value="Common Goldeneye">Common Goldeneye</option>
              <option value="Common Merganser">Common Merganser</option>
              <option value="Common Moorhen">Common Moorhen</option>
              <option value="Common Raven">Common Raven</option>
              <option value="Common Snipe">Common Snipe</option>
              <option value="Common Yellowthroat">Common Yellowthroat</option>
              <option value="Cooper's Hawk">Cooper's Hawk</option>
              <option value="Dark-eyed Junco">Dark-eyed Junco</option>
              <option value="Double-crested Cormorant">Double-crested Cormorant</option>
              <option value="Downy Woodpecker">Downy Woodpecker</option>
              <option value="Eurasian Collared-Dove">Eurasian Collared-Dove</option>
              <option value="European Starling">European Starling</option>
              <option value="Evening Grosbeak">Evening Grosbeak</option>
              <option value="Forster's Tern">Forster's Tern</option>
              <option value="Fox Sparrow">Fox Sparrow</option>
              <option value="Gadwall">Gadwall</option>
              <option value="Glaucous-winged Gull">Glaucous-winged Gull</option>
              <option value="Golden Eagle">Golden Eagle</option>
              <option value="Golden-crowned Kinglet">Golden-crowned Kinglet</option>
              <option value="Golden-crowned Sparrow">Golden-crowned Sparrow</option>
              <option value="Grasshopper Sparrow">Grasshopper Sparrow</option>
              <option value="Great Blue Heron">Great Blue Heron</option>
              <option value="Great Egret">Great Egret</option>
              <option value="Great Gray Owl">Great Gray Owl</option>
              <option value="Great Horned Owl">Great Horned Owl</option>
              <option value="Greater White-fronted Goose">Greater White-fronted Goose</option>
              <option value="Greater Yellowlegs">Greater Yellowlegs</option>
              <option value="Green Heron">Green Heron</option>
              <option value="Green-Winged Teal">Green-Winged Teal</option>
              <option value="Hairy Woodpecker">Hairy Woodpecker</option>
              <option value="Hermit Thrush">Hermit Thrush</option>
              <option value="Hermit Warbler">Hermit Warbler</option>
              <option value="Herring Gull">Herring Gull</option>
              <option value="Hooded Merganser">Hooded Merganser</option>
              <option value="Hooded Oriole">Hooded Oriole</option>
              <option value="House Finch">House Finch</option>
              <option value="House Sparrow">House Sparrow</option>
              <option value="House Wren">House Wren</option>
              <option value="Hutton's Vireo">Hutton's Vireo</option>
              <option value="Indigo Bunting">Indigo Bunting</option>
              <option value="Killdeer">Killdeer</option>
              <option value="Lark Sparrow">Lark Sparrow</option>
              <option value="Lawrence's Goldfinch">Lawrence's Goldfinch</option>
              <option value="Lazuli Bunting">Lazuli Bunting</option>
              <option value="Least Sandpiper">Least Sandpiper</option>
              <option value="Lesser Goldfinch">Lesser Goldfinch</option>
              <option value="Lewis's Woodpecker">Lewis's Woodpecker</option>
              <option value="Lincoln's Sparrow">Lincoln's Sparrow</option>
              <option value="Loggerhead Shrike">Loggerhead Shrike</option>
              <option value="Long-billed Dowitcher">Long-billed Dowitcher</option>
              <option value="MacGillivray's Warbler">MacGillivray's Warbler</option>
              <option value="Magnolia Warbler">Magnolia Warbler</option>
              <option value="Mallard">Mallard</option>
              <option value="Marsh Wren">Marsh Wren</option>
              <option value="Merlin">Merlin</option>
              <option value="Mourning Dove">Mourning Dove</option>
              <option value="Nashville Warbler">Nashville Warbler</option>
              <option value="Northern Flicker">Northern Flicker</option>
              <option value="Northern Goshawk">Northern Goshawk</option>
              <option value="Northern Harrier">Northern Harrier</option>
              <option value="Northern Mockingbird">Northern Mockingbird</option>
              <option value="Northern Pintail">Northern Pintail</option>
              <option value="Northern Pygmy-Owl">Northern Pygmy-Owl</option>
              <option value="Northern Rough-winged Swallow">Northern Rough-winged Swallow</option>
              <option value="Northern Shoveler">Northern Shoveler</option>
              <option value="Nuttall's Woodpecker">Nuttall's Woodpecker</option>
              <option value="Oak Titmouse">Oak Titmouse</option>
              <option value="Olive-sided Flycatcher">Olive-sided Flycatcher</option>
              <option value="Orange-crowned Warbler">Orange-crowned Warbler</option>
              <option value="Osprey">Osprey</option>
              <option value="Pacific Wren">Pacific Wren</option>
              <option value="Pacific-slope Flycatcher">Pacific-slope Flycatcher</option>
              <option value="Peregrine Falcon">Peregrine Falcon</option>
              <option value="Phainopepla">Phainopepla</option>
              <option value="Pied-billed Grebe">Pied-billed Grebe</option>
              <option value="Pileated Woodpecker">Pileated Woodpecker</option>
              <option value="Pine Siskin">Pine Siskin</option>
              <option value="Purple Finch">Purple Finch</option>
              <option value="Purple Martin">Purple Martin</option>
              <option value="Pygmy Nuthatch">Pygmy Nuthatch</option>
              <option value="Red Crossbill">Red Crossbill</option>
              <option value="Red-breasted Nuthatch">Red-breasted Nuthatch</option>
              <option value="Red-breasted Sapsucker">Red-breasted Sapsucker</option>
              <option value="Red-naped Sapsucker">Red-naped Sapsucker</option>
              <option value="Red-shouldered Hawk">Red-shouldered Hawk</option>
              <option value="Red-tailed Hawk">Red-tailed Hawk</option>
              <option value="Redwing">Redwing</option>
              <option value="Red-winged Blackbird">Red-winged Blackbird</option>
              <option value="Ridgway's Rail">Ridgway's Rail</option>
              <option value="Ring-billed Gull">Ring-billed Gull</option>
              <option value="Ring-necked Duck">Ring-necked Duck</option>
              <option value="Rock Pigeon">Rock Pigeon</option>
              <option value="Ruby-crowned Kinglet">Ruby-crowned Kinglet</option>
              <option value="Ruddy Duck">Ruddy Duck</option>
              <option value="Rufous Hummingbird">Rufous Hummingbird</option>
              <option value="Rusty Blackbird">Rusty Blackbird</option>
              <option value="Savannah Sparrow">Savannah Sparrow</option>
              <option value="Say's Phoebe">Say's Phoebe</option>
              <option value="Sharp-shinned Hawk">Sharp-shinned Hawk</option>
              <option value="Snowy Egret">Snowy Egret</option>
              <option value="Song Sparrow">Song Sparrow</option>
              <option value="Sora">Sora</option>
              <option value="Spotted Sandpiper">Spotted Sandpiper</option>
              <option value="Spotted Towhee">Spotted Towhee</option>
              <option value="Steller's Jay">Steller's Jay</option>
              <option value="Summer Tanager">Summer Tanager</option>
              <option value="Swainson's Hawk">Swainson's Hawk</option>
              <option value="Swainson's Thrush">Swainson's Thrush</option>
              <option value="Townsend's Warbler">Townsend's Warbler</option>
              <option value="Tree Swallow">Tree Swallow</option>
              <option value="Turkey Vulture">Turkey Vulture</option>
              <option value="Varied Thrush">Varied Thrush</option>
              <option value="Vaux's Swift">Vaux's Swift</option>
              <option value="Vesper Sparrow">Vesper Sparrow</option>
              <option value="Violet-green Swallow">Violet-green Swallow</option>
              <option value="Virginia Rail">Virginia Rail</option>
              <option value="Warbling Vireo">Warbling Vireo</option>
              <option value="Western Bluebird">Western Bluebird</option>
              <option value="Western Grebe">Western Grebe</option>
              <option value="Western Gull">Western Gull</option>
              <option value="Western Kingbird">Western Kingbird</option>
              <option value="Western Meadowlark">Western Meadowlark</option>
              <option value="Western Screech-Owl">Western Screech-Owl</option>
              <option value="Western Tanager">Western Tanager</option>
              <option value="Western Wood-Pewee">Western Wood-Pewee</option>
              <option value="White-breasted Nuthatch">White-breasted Nuthatch</option>
              <option value="White-crowned Sparrow">White-crowned Sparrow</option>
              <option value="White-faced Ibis">White-faced Ibis</option>
              <option value="White-tailed Kite">White-tailed Kite</option>
              <option value="White-throated Sparrow">White-throated Sparrow</option>
              <option value="White-throated Swift">White-throated Swift</option>
              <option value="Wild Turkey">Wild Turkey</option>
              <option value="Willow Flycatcher">Willow Flycatcher</option>
              <option value="Wilson's Snipe">Wilson's Snipe</option>
              <option value="Wilson's Warbler">Wilson's Warbler</option>
              <option value="Winter Wren">Winter Wren</option>
              <option value="Wood Duck">Wood Duck</option>
              <option value="Wrentit">Wrentit</option>
              <option value="Yellow Warbler">Yellow Warbler</option>
              <option value="Yellow-rumped Warbler">Yellow-rumped Warbler</option>
            </select>
            <div style="margin-bottom:5px;margin-top:15px;">
              Other/unlisted, separated by commas:
            </div>
            <!-- Species custom input -->
            <input type="text" id="unlisted" name="unlisted" placeholder="ex. Dodo,Black Swan,Green Peafowl" autocomplete="off" value="<?php echo $last_unlisted_species_input; ?>" style="width:100%" />
          </div>

          <!-- Species display half -->
          <div style="width:47%;display:flex;flex-direction:column;justify-content:end;">
            <textarea id="selected_species" readonly placeholder="Your species selections will appear here. Currently: None." name="selected_species" style="background:rgb(210,210,210);height:50%;width:100%;"><?php echo $last_listed_species_textarea; ?></textarea>
            <textarea id="unlisted_selected_species" readonly placeholder="Other/unlisted species selections will appear here. Currently: None." name="unlisted_selected_species" style="background:rgb(210,210,210);height:50%;width:100%;"><?php echo $last_unlisted_species_textarea; ?></textarea>
          </div>
        </div>

        <!-- Comments -->
        <div style="margin-bottom: 15px;">
          <div style="font-weight:bold;">Additional notes: (Is there anything unique about this recording?)</div>
          <textarea id="comments_text_area" name="comments" rows="4" cols="50" style="width:100%"><?php echo $last_comment; ?></textarea>
        </div>
        <input type="submit" name="submit" value="Submit">
      </form>
    </div>

    <!-- Last updated metadata -->
    <div style="margin-bottom: 15px">Last updated: <?php echo $last_updated_str; ?></div>

    <!-- Navigation buttons -->
    <div style="text-align:right">
      <a href='<?php echo "labeler.php?location=" . $_GET["location"] . "&sample=" . ($_GET["sample"] + 1); ?>'>Skip</a> |
      <a href="index.php">Back to Labeler Home</a>
    </div>
  </div>
</body>

</html>