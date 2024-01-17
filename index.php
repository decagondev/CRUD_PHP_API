<?php
// Set the content type to JSON
header('Content-Type: application/json');

// MySQL database credentials
$servername = 'mysql_server';
$username = 'mysql_username';
$password = 'mysql_password';
$database = 'mysql_database';

// Connect to MySQL database
$db = new mysqli($servername, $username, $password, $database);

// Check for errors in the connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Create contacts table if not exists
$query = 'CREATE TABLE IF NOT EXISTS contacts (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), email VARCHAR(255), phone VARCHAR(20))';
$db->query($query);

// Get HTTP method, path, and input data
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$input = json_decode(file_get_contents('php://input'), true);

// Define response array
$response = array();

// CRUD operations
switch ($method) {
    case 'GET':
        // Get contact(s)
        $id = intval($request[0] ?? 0);
        $response = ($id > 0) ? getContact($db, $id) : getAllContacts($db);
        break;

    case 'POST':
        // Create contact
        $response = createContact($db, $input);
        break;

    case 'PUT':
        // Update contact
        $id = intval($request[0] ?? 0);
        $response = updateContact($db, $id, $input);
        break;

    case 'DELETE':
        // Delete contact
        $id = intval($request[0] ?? 0);
        $response = deleteContact($db, $id);
        break;

    default:
        // Invalid method
        $response['error'] = 'Invalid method';
        break;
}

// Close the database connection
$db->close();

// Output the response as JSON
echo json_encode($response);

// CRUD functions
function getAllContacts($db) {
    $result = $db->query('SELECT * FROM contacts');
    $contacts = array();
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
    return $contacts;
}

function getContact($db, $id) {
    $statement = $db->prepare('SELECT * FROM contacts WHERE id = ?');
    $statement->bind_param('i', $id);
    $statement->execute();
    $result = $statement->get_result();
    return $result->fetch_assoc();
}

function createContact($db, $contact) {
    $statement = $db->prepare('INSERT INTO contacts (name, email, phone) VALUES (?, ?, ?)');
    $statement->bind_param('sss', $contact['name'], $contact['email'], $contact['phone']);
    $result = $statement->execute();
    return ($result) ? array('message' => 'Contact created successfully') : array('error' => 'Failed to create contact');
}

function updateContact($db, $id, $contact) {
    $statement = $db->prepare('UPDATE contacts SET name = ?, email = ?, phone = ? WHERE id = ?');
    $statement->bind_param('sssi', $contact['name'], $contact['email'], $contact['phone'], $id);
    $result = $statement->execute();
    return ($result) ? array('message' => 'Contact updated successfully') : array('error' => 'Failed to update contact');
}

function deleteContact($db, $id) {
    $statement = $db->prepare('DELETE FROM contacts WHERE id = ?');
    $statement->bind_param('i', $id);
    $result = $statement->execute();
    return ($result) ? array('message' => 'Contact deleted successfully') : array('error' => 'Failed to delete contact');

