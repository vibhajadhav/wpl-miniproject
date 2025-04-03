<?php
// db_connection.php (Database connection)
$servername = "localhost";
$username = "root"; // default username for MySQL
$password = ""; // default password for MySQL (if you have set one, use that)
$dbname = "pharmacy"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_POST['search'])) {
    $searchTerm = $_POST['searchTerm'];

    // Query to search in 'company' table based on the 'company' name
    $sql = "SELECT * FROM company WHERE company LIKE '%$searchTerm%'";
    $result = $conn->query($sql);

    $searchResults = "";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $searchResults .= "<div class='result-item'>
                    <h3>" . $row['company'] . "</h3>
                    <p>GST No: " . $row['gstno'] . "</p>
                    <p>Address: " . $row['c_address'] . "</p>
                  </div>";
        }
    } else {
        $searchResults = "No results found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Navbar or other content can be added here -->
    
    <div class="container">
        <!-- Search Bar -->
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2>Search Pharmacy Companies</h2>
                <form id="searchForm" method="POST">
                    <input type="text" id="searchTerm" name="searchTerm" class="form-control" placeholder="Search by company name" value="<?php echo isset($searchTerm) ? $searchTerm : ''; ?>">
                    <button type="submit" name="search" class="btn btn-primary mt-2">Search</button>
                </form>
                <div id="searchResults" class="mt-4">
                    <?php
                    if(isset($searchResults)) {
                        echo $searchResults;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            // Handle search form submission
            $('#searchForm').submit(function(e){
                e.preventDefault();
                var searchTerm = $('#searchTerm').val();
                
                $.ajax({
                    url: '', 
                    method: 'POST', 
                    data: {searchTerm: searchTerm, search: true}, 
                    success: function(response){
                        $('#searchResults').html(response);
                    }
                });
            });
        });
    </script>
</body>
</html>
