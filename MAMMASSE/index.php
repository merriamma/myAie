<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Schedules by Specialty</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 20px auto;
            background-color: #f4f4f4;
        }
        h1, h2 {
            text-align: center;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 2px solid #2c3e50;
        }
        .specialty-btn {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .specialty-btn:hover {
            background-color: #2980b9;
        }
        .loading {
            text-align: center;
            margin-top: 20px;
            font-style: italic;
            color: #7f8c8d;
        }
        #date-range {
            text-align: center;
            margin: 20px 0;
        }
        #date-range label {
            font-weight: bold;
            margin-right: 10px;
        }
        #date-range input {
            padding: 8px;
border: 1px solid #ddd;
border-radius: 4px;
}
table {
width: 100%;
border-collapse: collapse;
margin-top: 20px;
background-color: #fff;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
overflow: scroll;
}
th, td {
padding: 12px;
text-align: left;
border: 1px solid #ddd;
}
th {
background-color: #04AA6D;
color: #fff;
font-weight: bold;
}
td {
color: #000;
}
tr:nth-child(even) {
background-color: #f2f2f2;
}
table tr:hover {
background-color: #ddd;
}
tr:hover td {
background-color: #ddd;
}
tr:hover td.bordered {
border-bottom: 1px solid #ccc;
}
.bordered {
border-bottom: 1px solid #ccc;
border-right: 1px solid #ccc;
}
th.bordered {
border-right: 1px solid #fff;
border-bottom: 1px solid #fff;
}
tr:hover .bordered {
background-color: #ccc;
border-bottom: 1px solid #ccc;
border-right: 1px solid #ccc;
}
.bordered:hover {
background-color: #ccc;
border-right: 1px solid #ccc;
}
th.bordered:hover {
background-color: #ccc;
}
.hover-bordered:hover {
border-bottom: 1px solid #ccc;
}
tr.bordered {
border-bottom: 1px solid #ccc;
}
th.bordered {
border-right: 1px solid #fff;
}

/** Tables **/
table {
        font-family: verdana, arial, sans-serif;
        font-size: 11px;
        color: #333;
        border-width: 0px;
        border-color: #333333;
        border-collapse: collapse; 
    }
    th {
        font-family: verdana;
        font-size: 11px;
        font-weight: bold;
        border-bottom: 1px solid #333333;
    }
    tr.bordered th {
        border-bottom-width: 0px;
        border-right-width: 1px;
        border-style: solid;
        border-color: '#333333';
        padding: 0px;
        font-weight: bold;
        font-family: verdana;
        font-size: 11px;
    }
    tr.bordered td {
        border-right-width: 1px;
        border-bottom: 1px solid #333333;
        border-style: solid;
        border-color: '#333333';
        padding: 0px;
        font-weight: bold;
        font-family: verdana;
        font-size: 11px;
    }
    tr.bordered:hover {
        background-color: #ddd;
    }
    .bordered:hover {
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr.bordered:hover td {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr:hover .bordered {
        background-color: #ddd;
        border-right: 1px solid #ccc;
    }
    tr:hover td.bordered {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    th.bordered {
        border-right: 1px solid #fff;
        border-bottom: 1px solid #fff;
    }
    th.bordered:hover {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr.bordered th {
        border-right: 1px solid #fff;
        border-bottom: 1px solid #fff;
    }
    tr.bordered:hover {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr.bordered:hover td {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr.bordered:hover td.bordered {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr.bordered td.bordered {
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr.bordered:hover td.bordered {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr:hover td.bordered {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr.bordered:hover td.bordered {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr.bordered td.bordered {
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr.bordered:hover td.bordered {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr.bordered:hover td.bordered {
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr:hover td.bordered {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }
    tr:hover td.bordered {
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        border-left: 1px solid #ccc;
    }
    tr.bordered td.bordered {
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        border-left: 1px solid #ccc;
    }
    tr.bordered:hover td.bordered {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-left: 1px solid #ccc;
    }
    tr.bordered:hover td.bordered {
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-left: 1px solid #ccc;
    }
    tr.bordered:hover td.bordered {
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        border-left: 1px solid #ccc;
    }
    tr:hover td.bordered {
        background-color: #ddd;
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-left: 1px solid #ccc;
    }
    tr:hover td.bordered {
        border-bottom: 1px solid #ccc;
        border-right: 1px solid #ccc;
border-left: 1px solid #ccc;
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Exam Schedules by Specialty</h1>
    <div id="date-range">
    <label for="start-date">Start Date:</label>
    <input type="date" id="start-date" required>
</div>

<div id="specialty-buttons">
    <?php
    // Database connection details
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "myproject";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch specialties
    $specialties_query = "SELECT DISTINCT nom_specialite FROM specialite";
    $specialties_result = $conn->query($specialties_query);

    while ($row = $specialties_result->fetch_assoc()) {
        echo "<button class='specialty-btn' data-specialty='{$row['nom_specialite']}'>{$row['nom_specialite']}</button>";
    }

    $conn->close();
    ?>
</div>

<div id="exam-schedules"></div>

<script>
    $(document).ready(function() {
        $('.specialty-btn').click(function() {
            var specialty = $(this).data('specialty');
            var startDate = $('#start-date').val();

            if (!startDate) {
                alert("Please select a start date.");
                return;
            }

            $('#exam-schedules').html('<p class="loading">Loading exam schedule for ' + specialty + '...</p>');

            $.ajax({
                url: 'get_exams.php',
                type: 'POST',
                data: { 
                    specialty: specialty,
                    startDate: startDate
                },
                success: function(response) {
                    $('#exam-schedules').html(response);
                },
                error: function() {
                    $('#exam-schedules').html('<p>Error loading exam schedule.</p>');
                }
            });
        });
    });
</script>
</body>
</html>