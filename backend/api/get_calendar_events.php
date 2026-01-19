<?php
// backend/api/get_calendar_events.php
include_once '../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: x-user-id");

// Get User ID from header
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$user_id = isset($headers['x-user-id']) ? $headers['x-user-id'] : null;

// Also check GET param for testing/flexibility (though header is preferred)
if (!$user_id && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
}

if (!$user_id) {
    echo json_encode([]);
    exit;
}

try {
    // Fetch events user has joined
    $query = "SELECT e.id, e.title, e.event_date, e.event_time, e.category 
              FROM events e
              JOIN event_attendees ea ON e.id = ea.event_id
              WHERE ea.user_id = :uid";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':uid', $user_id);
    $stmt->execute();
    $events = $stmt->fetchAll();

    $calendarEvents = [];
    foreach ($events as $event) {
        // Map category to class name for color
        $className = 'event-default';
        if (stripos($event['category'], 'study') !== false)
            $className = 'study'; // Blue
        elseif (stripos($event['category'], 'social') !== false)
            $className = 'social'; // Pink
        elseif (stripos($event['category'], 'workshop') !== false)
            $className = 'workshop'; // Amber

        $start = $event['event_date'];
        if ($event['event_time']) {
            $start .= 'T' . $event['event_time'];
        }

        $calendarEvents[] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'start' => $start,
            'url' => 'event_details.html?id=' . $event['id'], // Click to view details
            'classNames' => ['legend-dot', $className], // Reusing CSS classes or just let frontend handle
            // FullCalendar uses 'classNames' or 'className'
            // We can also just send 'color' but let's send extendedProps if needed.
            // Actually, in the HTML, valid classes are 'study', 'social', 'workshop' combined with dot.
            // FullCalendar event rendering might need custom CSS or we just use backgroundColor.
            // Let's set 'backgroundColor' directly for simplicity and reliability.
            'backgroundColor' => getCategoryColor($event['category']),
            'borderColor' => getCategoryColor($event['category'])
        ];
    }

    echo json_encode($calendarEvents);

} catch (Throwable $e) {
    echo json_encode([]);
}

function getCategoryColor($category)
{
    if (stripos($category, 'study') !== false)
        return '#3B82F6'; // blue-500
    if (stripos($category, 'social') !== false)
        return '#EC4899'; // pink-500
    if (stripos($category, 'workshop') !== false)
        return '#F59E0B'; // amber-500
    return '#64748B'; // slate-500
}
?>