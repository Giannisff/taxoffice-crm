<?php
$pageTitle = "MyDiary";
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";

/* ================= USER ================= */

$userId = $_SESSION['user_id'] ?? 0;
if (!$userId) {
    header("Location: login.php");
    exit;
}

/* ================= SELECTED DATE ================= */

$selectedDate = $_GET['date'] ?? date("Y-m-d");
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
    $selectedDate = date("Y-m-d");
}

$selYear  = (int)date("Y", strtotime($selectedDate));
$selMonth = (int)date("n", strtotime($selectedDate));
$selDay   = (int)date("j", strtotime($selectedDate));

/* =========================================================
   SAVE NOTE
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_note'])) {
    $noteDate = $_POST['note_date'] ?? $selectedDate;
    $content  = $_POST['note_content'] ?? '';

    $noteDate = $mysqli->real_escape_string($noteDate);
    $content  = $mysqli->real_escape_string($content);

    $sql = "
        INSERT INTO diary_notes (user_id, note_date, content)
        VALUES ($userId, '$noteDate', '$content')
        ON DUPLICATE KEY UPDATE content = VALUES(content)
    ";
    $mysqli->query($sql);

    header("Location: mydiary.php?date=".$noteDate);
    exit;
}

/* =========================================================
   NEW EVENT
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {

    $eventDate  = $_POST['event_date'] ?? $selectedDate;
    $title      = trim($_POST['event_title'] ?? '');
    $start_time = $_POST['start_time'] ?? null;
    $end_time   = $_POST['end_time'] ?? null;
    $desc       = $_POST['event_desc'] ?? '';

    if ($title !== '') {

        $eventDate  = $mysqli->real_escape_string($eventDate);
        $title      = $mysqli->real_escape_string($title);
        $start_time = $start_time ? $mysqli->real_escape_string($start_time) : null;
        $end_time   = $end_time   ? $mysqli->real_escape_string($end_time)   : null;
        $desc       = $mysqli->real_escape_string($desc);

        $stmt = $mysqli->prepare("
            INSERT INTO diary_events (user_id, event_date, start_time, end_time, title, description)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isssss",
            $userId,
            $eventDate,
            $start_time,
            $end_time,
            $title,
            $desc
        );
        $stmt->execute();
    }

    header("Location: mydiary.php?date=".$eventDate);
    exit;
}

/* =========================================================
   DELETE EVENT
========================================================= */
if (isset($_GET['delete_event'])) {

    $eventId = (int)$_GET['delete_event'];

    $mysqli->query("
        DELETE FROM diary_events 
        WHERE id=$eventId AND user_id=$userId
    ");

    header("Location: mydiary.php?date=".$selectedDate);
    exit;
}

/* =========================================================
   LOAD DATA FOR MONTH
========================================================= */

$firstDayTs   = mktime(0, 0, 0, $selMonth, 1, $selYear);
$totalDays    = (int)date("t", $firstDayTs);
$startWeekday = (int)date("N", $firstDayTs);

$monthStart = date("Y-m-01", $firstDayTs);
$monthEnd   = date("Y-m-t", $firstDayTs);

/* CRM TASKS */
$tasksByDay = [];
$taskSql = "
    SELECT id, title, task_date, status
    FROM tasks
    WHERE task_date BETWEEN '$monthStart' AND '$monthEnd'
    ORDER BY task_date ASC
";
$taskRes = $mysqli->query($taskSql);
while ($row = $taskRes->fetch_assoc()) {
    $d = (int)date("j", strtotime($row['task_date']));
    $tasksByDay[$d][] = $row;
}

/* PERSONAL EVENTS */
$eventsByDay = [];
$evtSql = "
    SELECT *
    FROM diary_events
    WHERE user_id = $userId
      AND event_date BETWEEN '$monthStart' AND '$monthEnd'
    ORDER BY event_date ASC, start_time ASC
";
$evtRes = $mysqli->query($evtSql);
while ($row = $evtRes->fetch_assoc()) {
    $d = (int)date("j", strtotime($row['event_date']));
    $eventsByDay[$d][] = $row;
}

/* NOTES */
$noteRes = $mysqli->query("
    SELECT content
    FROM diary_notes
    WHERE user_id=$userId AND note_date='$selectedDate'
");
$noteRow = $noteRes->fetch_assoc();
$currentNote = $noteRow['content'] ?? "";

/* DAY EVENTS */
$dayEvents = [];
$dayEvt = $mysqli->query("
    SELECT *
    FROM diary_events
    WHERE user_id=$userId AND event_date='$selectedDate'
    ORDER BY start_time ASC, id ASC
");
while ($row = $dayEvt->fetch_assoc()) {
    $dayEvents[] = $row;
}

/* GREEK MONTHS */
$monthsGreek = [
    1=>"Ιανουάριος",2=>"Φεβρουάριος",3=>"Μάρτιος",4=>"Απρίλιος",
    5=>"Μάιος",6=>"Ιούνιος",7=>"Ιούλιος",8=>"Αύγουστος",
    9=>"Σεπτέμβριος",10=>"Οκτώβριος",11=>"Νοέμβριος",12=>"Δεκέμβριος"
];

$daysGreekShort = ["Δευ", "Τρι", "Τετ", "Πεμ", "Παρ", "Σαβ", "Κυρ"];

require "includes/header.php";
?>

<style>
.mydiary-layout {
    display: grid;
    grid-template-columns: 2fr 1.2fr;
    gap: 20px;
}

.mydiary-calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}

.mydiary-day-name {
    font-size: 13px;
    text-align:center;
    color:#6b7280;
}

.mydiary-cell {
    background:#fff;
    border-radius:10px;
    padding:6px;
    min-height:80px;
    box-shadow:0 1px 3px rgba(15,23,42,0.08);
}

.mydiary-day-number a { text-decoration:none; color:inherit; }

.mydiary-day-number .selected {
    background:#2563eb;
    color:white;
    padding:2px 8px;
    border-radius:999px;
}

.mydiary-item { font-size:12px; display:flex; align-items:center; }

.mydiary-dot { width:8px; height:8px; border-radius:50%; margin-right:4px; }

.dot-inprogress { background:#fd7e14; }
.dot-completed  { background:#28a745; }
.dot-pending    { background:#dc3545; }
.dot-personal   { background:#2563eb; }

.mydiary-side-card {
    background:#fff;
    padding:14px 16px;
    border-radius:12px;
    margin-bottom:15px;
    box-shadow:0 1px 4px rgba(0,0,0,0.1);
}

.mydiary-note-textarea {
    width:100%;
    min-height:100px;
    border:1px solid #d1d5db;
    border-radius:8px;
    padding:8px;
    font-size:13px;
}

/* BIG NOTES */
.big-note-area {
    min-height:250px !important;
}
@media(max-width:900px){
    .big-note-area { min-height:300px !important; }
    .mydiary-layout { grid-template-columns:1fr; }
}

.mydiary-event-row {
    padding:10px 0;
    border-bottom:1px solid #e5e7eb;
    display:flex;
    gap:12px;
}

.mydiary-event-row:last-child { border-bottom:none; }

.mydiary-event-box {
    flex:1;
    background:#f8fafc;
    padding:10px;
    border-radius:10px;
}

.mydiary-event-time { color:#475569; font-size:14px; }

.mydiary-event-actions a {
    font-size:18px;
    display:block;
    margin-bottom:6px;
}
</style>
<div class="page-container">

    <h2 style="margin-bottom:15px;">
        MyDiary – <?= $monthsGreek[$selMonth] . ' ' . $selYear ?>
    </h2>

    <div class="mydiary-layout">

        <!-- ======================= ΑΡΙΣΤΕΡΑ: ΜΗΝΙΑΙΟ CALENDAR ======================= -->
        <!-- ΑΡΙΣΤΕΡΑ: LIST VIEW CALENDAR -->
<div>

    <!-- Πλοήγηση μήνα -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <?php
            $prevMonth = $selMonth - 1;
            $prevYear  = $selYear;
            if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

            $nextMonth = $selMonth + 1;
            $nextYear  = $selYear;
            if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

            $prevDate = sprintf("%04d-%02d-01", $prevYear, $prevMonth);
            $nextDate = sprintf("%04d-%02d-01", $nextYear, $nextMonth);
        ?>
        <a class="cal-btn" href="mydiary.php?date=<?= $prevDate ?>">◀ Προηγούμενος</a>
        <div style="font-weight:600; font-size:17px;">
            <?= $monthsGreek[$selMonth] . ' ' . $selYear ?>
        </div>
        <a class="cal-btn" href="mydiary.php?date=<?= $nextDate ?>">Επόμενος ▶</a>
    </div>

    <!-- LIST VIEW -->
    <div class="mydiary-list">

        <?php for ($d = 1; $d <= $totalDays; $d++): ?>
            <?php
                $thisDate = sprintf("%04d-%02d-%02d", $selYear, $selMonth, $d);
                $isSelected = ($thisDate === $selectedDate);
                $dayName = date("l", strtotime($thisDate));
                $greekNames = [
                    "Monday" => "Δευτέρα",
                    "Tuesday" => "Τρίτη",
                    "Wednesday" => "Τετάρτη",
                    "Thursday" => "Πέμπτη",
                    "Friday" => "Παρασκευή",
                    "Saturday" => "Σάββατο",
                    "Sunday" => "Κυριακή"
                ];
                $grDayName = $greekNames[$dayName];
            ?>

            <div class="mydiary-list-day" style="
                background:#fff;
                padding:12px 15px;
                margin-bottom:12px;
                border-radius:12px;
                box-shadow:0 1px 4px rgba(0,0,0,0.1);
            ">

                <!-- Τίτλος ημέρας -->
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size:16px; font-weight:600;">
                        <?= $grDayName ?> <?= $d ?>
                    </div>

                    <a href="mydiary.php?date=<?= $thisDate ?>"
                       style="text-decoration:none; font-size:13px; color:#2563eb;">
                        ➜ Μετάβαση στην ημέρα
                    </a>
                </div>

                <!-- CRM TASKS -->
                <?php if (!empty($tasksByDay[$d])): ?>
                    <div style="margin-top:8px;">
                        <?php foreach ($tasksByDay[$d] as $t): ?>
                            <?php
                                $dotClass = 'dot-inprogress';
                                if ($t['status'] === 'Ολοκληρωμένη')       $dotClass = 'dot-completed';
                                elseif ($t['status'] === 'Αναμονή εξόφλησης') $dotClass = 'dot-pending';
                            ?>
                            <div style="display:flex; margin-top:5px; font-size:14px; align-items:center;">
                                <span class="mydiary-dot <?= $dotClass ?>" style="margin-right:6px;"></span>
                                <span><?= htmlspecialchars($t['title']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- PERSONAL EVENTS -->
                <?php if (!empty($eventsByDay[$d])): ?>
                    <div style="margin-top:8px;">
                        <?php foreach ($eventsByDay[$d] as $e): ?>
                            <?php
                                $st = $e["start_time"] ? substr($e["start_time"], 0, 5) : "";
                                $et = $e["end_time"] ? substr($e["end_time"], 0, 5) : "";
                            ?>
                            <div style="display:flex; margin-top:5px; font-size:14px; align-items:center;">
                                <span class="mydiary-dot dot-personal" style="margin-right:6px;"></span>
                                <span>
                                    <?= $st ?><?= $et ? "–".$et : "" ?> — 
                                    <strong><?= htmlspecialchars($e['title']) ?></strong>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Αν δεν υπάρχει τίποτα -->
                <?php if (empty($tasksByDay[$d]) && empty($eventsByDay[$d])): ?>
                    <div style="font-size:13px; color:#6b7280; margin-top:6px;">
                        (Καμία εργασία ή ραντεβού)
                    </div>
                <?php endif; ?>

            </div>
        <?php endfor; ?>

    </div>

</div>


        <!-- ======================= ΔΕΞΙΑ: ΣΗΜΕΙΩΣΕΙΣ + ΡΑΝΤΕΒΟΥ + ΝΕΟ ΡΑΝΤΕΒΟΥ ======================= -->
        <div>

            <!-- ΜΕΓΑΛΕΣ ΣΗΜΕΙΩΣΕΙΣ -->
            <div class="mydiary-side-card">
                <h3 style="margin-bottom:10px;">
                    📝 Σημειώσεις για <?= greekDateFromDb($selectedDate) ?>
                </h3>

                <form method="post">
                    <input type="hidden" name="note_date" value="<?= $selectedDate ?>">

                    <textarea name="note_content"
                              class="mydiary-note-textarea big-note-area"
                              placeholder="Γράψε άνετα τις σημειώσεις της ημέρας..."><?= htmlspecialchars($currentNote) ?></textarea>

                    <button type="submit"
                            name="save_note"
                            class="filter-btn"
                            style="margin-top:8px; width:100%;">
                        💾 Αποθήκευση σημειώσεων
                    </button>
                </form>
            </div>

            <!-- ΡΑΝΤΕΒΟΥ ΗΜΕΡΑΣ -->
            <div class="mydiary-side-card">
                <h3 style="font-size:18px; font-weight:600; color:#1e293b;">
                    📆 Ραντεβού: <span style="color:#2563eb;"><?= greekDateFromDb($selectedDate) ?></span>
                </h3>

                <?php if ($dayEvents): ?>

                    <?php foreach ($dayEvents as $e): ?>
                        <?php 
                            $st = $e['start_time'] ? substr($e['start_time'],0,5) : "";
                            $et = $e['end_time']   ? substr($e['end_time'],0,5)   : "";
                        ?>

                        <div class="mydiary-event-row">

                            <div class="mydiary-event-box">
                                <div class="mydiary-event-time"><?= $st ?><?= $et ? " – " . $et : "" ?></div>

                                <div class="mydiary-event-title">
                                    <?= htmlspecialchars($e['title']) ?>
                                </div>

                                <?php if ($e['description']): ?>
                                    <div style="font-size:13px; color:#64748b;">
                                        <?= nl2br(htmlspecialchars($e['description'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mydiary-event-actions">

                                <!-- EDIT -->
                                <a href="#"
                                   onclick='openEditModal({
                                       id:"<?= $e["id"] ?>",
                                       title:"<?= addslashes($e["title"]) ?>",
                                       start_time:"<?= $e["start_time"] ?>",
                                       end_time:"<?= $e["end_time"] ?>",
                                       description:"<?= addslashes($e["description"]) ?>"
                                   })'
                                   title="Επεξεργασία">
                                   ✏️
                                </a>

                                <!-- DELETE -->
                                <a href="mydiary.php?date=<?= $selectedDate ?>&delete_event=<?= $e['id'] ?>"
                                   onclick="return confirm('Διαγραφή ραντεβού;')"
                                   title="Διαγραφή">
                                   🗑
                                </a>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>
                    <div style="font-size:13px; color:#6b7280;">
                        Δεν υπάρχουν ραντεβού για αυτή τη μέρα.
                    </div>
                <?php endif; ?>

            </div>

            <!-- ΝΕΟ ΡΑΝΤΕΒΟΥ -->
            <div class="mydiary-side-card">
                <h3>➕ Νέο ραντεβού</h3>

                <form method="post">
                    <input type="hidden" name="event_date" value="<?= $selectedDate ?>">

                    <div style="margin-bottom:6px;">Τίτλος</div>
                    <input type="text"
                           name="event_title"
                           class="mydiary-small-input"
                           placeholder="π.χ. Ραντεβού με πελάτη"
                           required>

                    <div style="display:flex; gap:8px;">
                        <div style="flex:1;">
                            <div style="margin-bottom:4px;">Ώρα από</div>
                            <input type="time" name="start_time" class="mydiary-small-input">
                        </div>

                        <div style="flex:1;">
                            <div style="margin-bottom:4px;">Ώρα έως</div>
                            <input type="time" name="end_time" class="mydiary-small-input">
                        </div>
                    </div>

                    <div style="margin:6px 0 4px;">Περιγραφή</div>
                    <textarea name="event_desc"
                              class="mydiary-note-textarea"
                              style="min-height:70px;"
                              placeholder="Σύντομη περιγραφή..."></textarea>

                    <button type="submit" name="save_event" class="filter-btn" style="margin-top:8px; width:100%;">
                        ➕ Καταχώρηση ραντεβού
                    </button>

                </form>
            </div>
        </div>

    </div>

</div>
<!-- ======================= MODAL EDIT EVENT ======================= -->
<div id="editEventModal"
     class="modal"
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.5); z-index:9999;">

    <div class="modal-content"
         style="background:white; width:90%; max-width:420px;
                margin:80px auto; padding:20px; border-radius:12px;
                box-shadow:0 4px 30px rgba(0,0,0,0.25);">

        <h3 style="margin-top:0;">✏️ Επεξεργασία Ραντεβού</h3>

        <form id="editEventForm">

            <input type="hidden" id="edit_event_id" name="id">

            <label>Τίτλος</label>
            <input type="text" id="edit_event_title" name="title"
                   style="width:100%; padding:10px; margin-bottom:10px;" required>
<label>Ημερομηνία</label>
<input type="date" name="event_date" id="edit_event_date">

            <label>Ώρα από</label>
            <input type="time" id="edit_event_start" name="start_time"
                   style="width:100%; padding:10px; margin-bottom:10px;">

            <label>Ώρα έως</label>
            <input type="time" id="edit_event_end" name="end_time"
                   style="width:100%; padding:10px; margin-bottom:10px;">

            <label>Περιγραφή</label>
            <textarea id="edit_event_desc" name="description"
                      style="width:100%; height:100px; padding:10px; margin-bottom:10px;"></textarea>

            <button type="submit"
                    style="background:#28a745; padding:12px; color:white;
                           width:100%; border:none; border-radius:8px;">
                ✔ Αποθήκευση
            </button>

            <button type="button"
                    onclick="closeEditModal()"
                    style="background:#6c757d; padding:10px; color:white;
                           width:100%; border:none; border-radius:8px; margin-top:10px;">
                ✖ Άκυρο
            </button>

        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById("edit_event_id").value    = data.id;
    document.getElementById("edit_event_title").value = data.title;
    document.getElementById("edit_event_start").value = data.start_time;
    document.getElementById("edit_event_end").value   = data.end_time;
    document.getElementById("edit_event_desc").value  = data.description;

    document.getElementById("editEventModal").style.display = "block";
}

function closeEditModal() {
    document.getElementById("editEventModal").style.display = "none";
}

document.getElementById("editEventForm").addEventListener("submit", function(e){
    e.preventDefault();

    let formData = new FormData(this);

    fetch("update_event.php", {
        method: "POST",
        body: formData
    }).then(r => r.text())
      .then(resp => {
        alert("Το ραντεβού ενημερώθηκε!");
        location.reload();
      });
});
</script>

<?php require "includes/footer.php"; ?>
