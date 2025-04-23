<?php
session_start();
include 'connection.php';

date_default_timezone_set('Asia/Manila');

include 'connection.php';

$user = isset($_SESSION['Username']) ? $_SESSION['Username'] : "";

$sql_logo = "SELECT Logo FROM c3_logo WHERE Username = ?";
$stmt_logo = $conn->prepare($sql_logo);
$stmt_logo->bind_param("s", $user);
$stmt_logo->execute();
$result_logo = $stmt_logo->get_result();
$logo_row = $result_logo->fetch_assoc();
$logoPath = !empty($logo_row['Logo']) ? $logo_row['Logo'] : "images/PCDRRMO_LOGO1.png";
$faviconPath = !empty($logo_row['Logo']) ? $logo_row['Logo'] : "images/Title.png";
$stmt_logo->close();

$sql_addaccount = "SELECT Logo FROM c3_addaccount WHERE Username = ?";
$stmt_addaccount = $conn->prepare($sql_addaccount);
$stmt_addaccount->bind_param("s", $user);
$stmt_addaccount->execute();
$result_addaccount = $stmt_addaccount->get_result();

if ($row_addaccount = $result_addaccount->fetch_assoc()) {
    $addaccountLogoPath = $row_addaccount['Logo'];
    $showLogo = !empty($addaccountLogoPath);
} else {
    $showLogo = false;
}

$stmt_addaccount->close();

$sql_select = "SELECT Involve FROM c3_involve";
$stmt = $conn->prepare($sql_select);
$stmt->execute();
$result = $stmt->get_result();

$categories = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['Involve']; 
    }
} else {
    $categories = ['No Involvements Found']; 
}


$sql_usernames = "SELECT Username FROM c3_addaccount WHERE Position = 'Barangay' AND Username LIKE 'BRGY_%' ORDER BY Username ASC";
$result_usernames = $conn->query($sql_usernames);

$usernames = array();
if ($result_usernames->num_rows > 0) {
    while ($row = $result_usernames->fetch_assoc()) {
        $usernames[] = $row["Username"];
    }
}

// Convert the PHP array to a JavaScript array
$usernames_js = json_encode($usernames);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCDRRMO | C3 Data Analytics</title>
    <link rel="stylesheet" href="C3_DataAnalytics_46.css">
    <link rel="shortcut icon" type="image/png" href="<?php echo $faviconPath; ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>




   
</head>
<body>




<!-- navbar -->
<nav class="navbar">
    <div class="logo_item">
        <img src="<?php echo htmlspecialchars($faviconPath); ?>" alt="">Pasig City DRRMO
    </div>




    <div class="navbar_content">

<!-- LIVE TIME AND WEATHER FORECAST -->
<div id="weather">Loading Weather Data...</div>

<script>
function fetchWeather(latitude, longitude) {
const url = `https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current_weather=true&hourly=temperature_2m,relative_humidity_2m,wind_speed_10m`;

fetch(url)
.then(response => response.json())
.then(data => {
    const temperature = data.current_weather.temperature;
    const windSpeed = data.current_weather.windspeed;
    const weatherCode = data.current_weather.weathercode;

    let weatherIcon = "";
    if (weatherCode === 0) {
        weatherIcon = "☀️"; // CLEAR SKY 
    } else if (weatherCode >= 1 && weatherCode <= 3) { 
        weatherIcon = "⛅"; // PARTLY CLOUDY  
    } else if (weatherCode >= 51 && weatherCode <= 67) { 
        weatherIcon = "🌧️"; // LIGHT TO MODERATE RAIN 
    } else if (weatherCode >= 80 && weatherCode <= 86) { 
        weatherIcon = "🌧️"; // RAIN SHOWERS
    } else if (weatherCode >= 95 && weatherCode <= 99) { 
        weatherIcon = "⛈️"; // THUNDERSTORM
    } else {
        weatherIcon = "❄️"; // SNOW OR OTHER WEATHER CONDITIONS
    }

    const weatherString = `
        <div>
            <span class="weather-icon">${weatherIcon}</span>
            <span class="weather-info">
                <strong>${temperature}°C</strong>
                <br>Wind: ${windSpeed} m/s
            </span>
        </div>
    `;
    
    document.getElementById('weather').innerHTML = weatherString;
})
.catch(error => {
    console.error('Error fetching the weather data:', error);
    document.getElementById('weather').innerHTML = 'Failed to load weather data.';
});
}

function getLocation() {
if (navigator.geolocation) {
navigator.geolocation.getCurrentPosition(
    position => {
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;
        fetchWeather(latitude, longitude);
    },
    error => {
        console.error('Error getting location:', error);
        document.getElementById('weather').innerHTML = 'Location access denied. Unable to load weather data.';
    }
);
} else {
document.getElementById('weather').innerHTML = 'Geolocation is not supported by this browser.';
}
}

getLocation();
</script>

<div id="clock"></div>

<script>
function updateClock() {
const now = new Date();
let hours = now.getHours();
let minutes = now.getMinutes();
let seconds = now.getSeconds();

// Add leading zeros to minutes and seconds
minutes = minutes < 10 ? '0' + minutes : minutes;
seconds = seconds < 10 ? '0' + seconds : seconds;

// Display the time
const timeString = hours + ':' + minutes + ':' + seconds;
document.getElementById('clock').textContent = timeString;
}

// Update the clock every second
setInterval(updateClock, 1000);

// Initial call to display the time immediately
updateClock();
</script>

<div class="logo-container">
            <?php if ($showLogo): ?>
                <img src="<?php echo htmlspecialchars($addaccountLogoPath); ?>" alt="User Logo" class="user-logo" onclick="toggleDropdown()">
                <div id="dropdownMenu" class="dropdown-content">
                    <a href="C3_Settings.php"> 
                    <div class="brgy-profile-icon-container">
                    <img src="images/BRGYSettings.png" alt="Settings Icon"  style="width: 23px; height: 23px; margin-right: 15px;" class="brgy-settings-icon"> Settings
                    </div>
                    </a>
            </div>
            </div>
            <?php endif; ?>

            <script>
                function toggleDropdown() {
                var dropdown = document.getElementById("dropdownMenu");
                dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
            }

                window.onclick = function(event) {
            if (!event.target.matches('.user-logo')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
            if (openDropdown.style.display === "block") {
                openDropdown.style.display = "none";
                    }
                }
            }
            }
            </script>
            
    </div>

</nav>

<!-- sidebar -->
<nav class="sidebar">
<div class="menu_content">

    <!-- DASHBOARD -->
    <div class="menu_dashboard">
    <ul>
        <li>
            <a href="C3_Dashboard.php">Dashboard</a>
        </li>
    </ul>
    </div>  


<!-- REPORT A FIRE -->
<ul class="menu_items">
    <div class="menu_title menu_fire"></div>
    <!-- LOCATE -->
    <li class="item">
        <a href="C3_Locate.php" class="nav_link submenu_item">
            <span class="navlink_icon">
                <img src="images/Locate.png" class="navlink_image">
            </span>
            <span class="navlink">Locate</span>
        </a>
    </li>
    <!-- BARANGAY REPORTS -->
    <li class="item">
        <a href="C3_BReports.php" class="nav_link submenu_item">
            <span class="navlink_icon">
                <img src="images/Barangay_Reports.png" class="navlink_image">
            </span>
            <span class="navlink">Barangay Reports</span>
        </a>
    </li>
    <!-- MOBILE REPORTS -->
    <li class="item">
        <a href="C3_MReports.php" class="nav_link submenu_item">
            <span class="navlink_icon">
                <img src="images/Cellphone.png" style="width:25px; height:25px;" class="navlink_image">
            </span>
            <span class="navlink">Mobile Reports</span>
        </a>
    </li>
</ul>


    <!-- DISPATCH -->
    <ul class="menu_items">
        <div class="menu_title menu_dispatch"></div>
        <!-- ONGOING -->
        <li class="item">
            <a href="C3_Ongoing.php" class="nav_link submenu_item">
                <span class="navlink_icon">
                    <img src="images/Ongoing.png" class="navlink_image">
                </span>
                <span class="navlink">Ongoing</span>
            </a>
        </li>
        <!-- REQUEST -->
        <li class="item">
            <a href="C3_RequestSBar.php" class="nav_link submenu_item">
                <span class="navlink_icon">
                    <img src="images/request2.png" class="navlink_image">
                </span>
                <span class="navlink">Request</span>
            </a>
        </li>
        <!-- COMPLETED -->
        <li class="item">
            <a href="C3_Completed.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Completed.png" class="navlink_image">
                </span>
                <span class="navlink">Completed</span>
            </a>
        </li>
        <!-- HELPOUT -->
        <li class="item">
            <a href="C3_HelpOut.php" class="nav_link submenu_item">
                <span class="navlink_icon">
                    <img src="images/HelpOut.png" class="navlink_image">
                </span>
                <span class="navlink">Help Out</span>
            </a>
        </li>
    </ul>




<!-- INCIDENT REPORT -->
<ul class="menu_items">
    <div class="menu_title menu_incident"></div>
    <!-- FILE -->
    <li class="item">
        <a href="C3_File.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/File.png" class="navlink_image">
            </span>
            <span class="navlink">File</span>
        </a>
    </li>
    <!-- RECORDS -->
    <li class="item">
        <a href="C3_Records.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Records.png" class="navlink_image">
            </span>
            <span class="navlink">Records</span>
        </a>
    </li>
</ul>




<!-- MAINTENANCE -->
<ul class="menu_items">
    <div class="menu_title menu_maintenance"></div>
    <!-- ANNOUNCEMENT -->
    <li class="item">
        <a href="C3_Announcements.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Announcement.png" class="navlink_image">
            </span>
            <span class="navlink">Announcement</span>
        </a>
    </li>

    <!-- Account -->
    <li class="item">
            <a href="C3_Account.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Account.png" class="navlink_image">
                </span>
                <span class="navlink">  Accounts </span>
            </a>
        </li>

    <!-- Contact Information -->
    <li class="item">
            <a href="C3_ContactInfo.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Contact.png" style="width:25px; height:25px;" class="navlink_image">
                </span>
                <span class="navlink"> Hotlines </span>
            </a>
        </li>




<!-- ANALYTICS -->
<ul class="menu_items">
    <div class="menu_title menu_data"></div>
    <!-- DATA ANALYTICS -->
    <li class="item">
        <a href="C3_DataAnalytics.php" class="nav_link active">
            <span class="navlink_icon">
                <img src="images/Data_Analytics.png" class="navlink_image">
            </span>
            <span class="navlink"> Data Analytics</span>
        </a>
    </li>
    <!-- REPORTS -->
    <li class="item">
        <a href="C3_Reports.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Reports.png" class="navlink_image">
            </span>
            <span class="navlink">Audit Trail</span>
        </a>
    </li>
</ul>




<!-- LOGOUT -->
<div class="bottom_content">
            <div class="bottom collapse_sidebar">
            <span onclick="logout()"> Logout &nbsp; <img src="images/Logout.png" class="logout_icon"></span>
            </div>
        </div>
    </nav>




    <script>
    function logout() {
        if (confirm("Are you sure you want to logout?")) {
            var username = "<?php echo $_SESSION["Username"]; ?>";
            var actionMessage = encodeURIComponent(username) + " just logged out";
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "log_action.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        console.log("Logout action logged successfully.");
                    } else {
                        console.error("Error logging logout action: " + xhr.statusText);
                    }
                    window.location.href = "index.php";
                }
            };
            xhr.send("action=" + actionMessage);
        }
    }
</script>




<!-- Home Section -->
<section class="home-section">
    <h1>DATA ANALYTICS</h1>




<!-- Bar Chart 1 -->
<div class="chart-container">
    <div class="header-button-container">
        <div class="header2">
            <h2>MONTHLY FIRE ALERT</h2>
        </div>
        <div class="prev-next">
        <button class="pdf-button" onclick="generatePDFMONTHLYBARANGAY(currentYear)">PDF</button>
            <button onclick="prevMonthly()">PREV</button>
            <button onclick="nextMonthly()">NEXT</button>
        </div>
    </div>
    <canvas id="barChart"></canvas>
</div>
<br>




<div class="chart-row">
    <!-- Line 1 Chart -->
    <div class="chart-container2">
        <div class="header-button-container2">
            <div class="header2-2">
                <h2>MONTHLY FIRE ALERT PER BARANGAY</h2>
            </div>
            <div class="prev-next2">
            <button class="pdf-button" onclick="generatePDFMONTHLY(currentYear, currentMonthIndex + 1)">PDF</button>
                <button onclick="prevMonthlyBarangay()">PREV</button>
                <button onclick="nextMonthlyBarangay()">NEXT</button>
            </div>
        </div>
        <canvas id="doughnutChart1"></canvas>
    </div>




    <!-- Line 2 Chart -->
    <div class="chart-container2">
    <div class="header-button-container2">
        <div class="header2-2">
            <h2>YEARLY FIRE ALERT PER BARANGAY</h2>
        </div>
        <div class="prev-next2">
            <button class="pdf-button" onclick="generatePDFYEARLY(currentYear)">PDF</button>
            <button onclick="prevYearlyBarangay()">PREV</button>
            <button onclick="nextYearlyBarangay()">NEXT</button>
        </div>
    </div>
    <canvas id="doughnutChart2"></canvas>
</div>

</div>
<br>




<!-- Bar Chart 2 -->
<div class="chart-container">
    <div class="header-button-container">
        <div class="header2">
            <h2>YEARLY FIRE ALERT PER INVOLVED</h2>
        </div>
        <div class="prev-next">
        <button class="pdf-button" onclick="generatePDF(currentYear)">PDF</button>
            <button onclick="prevYearlyInvolved()">PREV</button>
            <button onclick="nextYearlyInvolved()">NEXT</button>
        </div>
    </div>
    <canvas id="lineChart"></canvas>
</div>




</section>




 <script>

function showDataInfo(button) {
    var row = button.closest('tr'); // Find the closest row to the clicked button
    var id = row.cells[0].innerText; // Get the text from the first cell of the row

    document.getElementById('pdfId').value = id; // Set the value of an element with id 'pdfId'

    // Call generatePDF with the id parameter
    generatePDF(id);
}

function generatePDF(id) {
    // Fetch the PDF content from the server
    fetch('print-details-analytics.php?pdf=true&id=' + id)
        .then(response => response.text())
        .then(pdfContent => {
            // Once the content is fetched, open a new window with the PDF content
            var pdfWindow = window.open("", "_blank");
            pdfWindow.document.open();
            pdfWindow.document.write("<html><body><pre>" + pdfContent + "</pre></body></html>");
            pdfWindow.document.close();
        })
        .catch(error => {
            console.error('Error fetching PDF content:', error);
        });
}

function generatePDF(year) {
    const url = `print-details-analytics.php?year=${year}`;
    window.open(url, '_blank');
}

function generatePDFYEARLY(year) {
    const url = `print-details-byearly.php?year=${year}`;
    window.open(url, '_blank');
}

function generatePDFMONTHLYBARANGAY(year) {
    const url = `print-details-bmonthly.php?year=${year}`;
    window.open(url, '_blank');
}


function generatePDFMONTHLY(year, month) {
    const url = `print-details-monthlyb.php?year=${year}&month=${month}`;
    window.open(url, '_blank');
}













    let currentDate = new Date();
    let currentYear = currentDate.getFullYear();
    let monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    let currentMonthIndex = currentDate.getMonth();
    let currentMonth = monthNames[currentMonthIndex];


    const fireAlertsData = {};


    function generateRandomData() {
        return Array.from({ length: 12 }, () => Math.floor(Math.random() * 50) + 1);
    }


    async function fetchFireAlertsData() {
        try {
            const response = await fetch('fetch_data.php');
            if (!response.ok) {
                throw new Error(`Error fetching data: ${response.statusText}`);
            }
            const data = await response.json();
            const monthlyData = new Array(12).fill(0);


            Object.keys(data).forEach(month => {
                const [year, monthIndex] = month.split('-').map(Number);
                if (year === currentYear) {
                    monthlyData[monthIndex - 1] += data[month];
                }
            });


            return monthlyData;
        } catch (error) {
            console.error('Error fetching fire alerts data:', error);
            return Array(12).fill(0);
        }
    }


    async function initializeChartData() {
        try {
            const data = await fetchFireAlertsData();
            fireAlertsData[currentYear] = data;


            const ctxBar = document.getElementById('barChart').getContext('2d');
            barChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: generateLabels(currentYear),
                    datasets: [{
                        label: `Monthly Fire Alert - ${currentYear}`,
                        data: fireAlertsData[currentYear],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error initializing chart data:', error);
        }
    }


    document.addEventListener('DOMContentLoaded', (event) => {
        initializeChartData();
    });


    function updateChart() {
        fetchFireAlertsData().then(data => {
            fireAlertsData[currentYear] = data;
            barChart.data.labels = generateLabels(currentYear);
            barChart.data.datasets[0].data = fireAlertsData[currentYear];
            barChart.options.plugins.title.text = `Monthly Fire Alert - ${currentYear}`;
            barChart.data.datasets[0].label = `Monthly Fire Alert - ${currentYear}`; // Update label as well
            barChart.update();
        }).catch(error => {
            console.error('Error updating chart data:', error);
        });
    }


    function prevMonthly() {
        currentYear--;
        updateChart();
    }


    function nextMonthly() {
        currentYear++;
        updateChart();
    }








const ctxDoughnut1 = document.getElementById('doughnutChart1').getContext('2d');
function getCurrentYearMonth() {
    const date = new Date();
    const year = date.getFullYear();
    const month = date.toLocaleString('default', { month: 'long' });
    return { year, month };
}


function generateLabels(year) {
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return months.map(month => `${month} ${year}`);
    }


const { year, month } = getCurrentYearMonth();

const usernames = <?php echo $usernames_js; ?>;

const doughnutChart1 = new Chart(ctxDoughnut1, {
    type: 'bar',
    data: {
        labels: usernames, // Use the fetched usernames as labels

        datasets: [{
            label: `Monthly Fire Alert per Barangay`,
            data: [],
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 60,
                    minRotation: 60,
                    font: {
                        size: 13
                    }
                }
            }
        },
        plugins: {
            title: {
                display: true,
                text: `Monthly Fire Alert per Barangay - ${currentMonth} ${currentYear}`
            }
        }
    }
});

updateChartData(year, month);


function updateChartData(year, month) {
    fetch(`fetch_barangay.php?year=${year}&month=${month}`)
        .then(response => response.json())
        .then(data => {
            doughnutChart1.data.datasets[0].data = Object.values(data);
            doughnutChart1.update();
        })
        .catch(error => console.error('Error fetching data:', error));
}

function updateDoughnutChart1() {
    doughnutChart1.options.plugins.title.text = `Monthly Fire Alert per Barangay - ${currentMonth} ${currentYear}`;
    doughnutChart1.update();
}

updateChartData(currentYear, currentMonthIndex + 1);

function prevMonthlyBarangay() {
    currentMonthIndex--;
    if (currentMonthIndex < 0) {
        currentMonthIndex = 11; 
        currentYear--;
    }
    currentMonth = monthNames[currentMonthIndex];
    updateChartData(currentYear, currentMonthIndex + 1);
    updateDoughnutChart1();
}

function nextMonthlyBarangay() {
    currentMonthIndex++;
    if (currentMonthIndex > 11) {
        currentMonthIndex = 0; // January
        currentYear++;
    }
    currentMonth = monthNames[currentMonthIndex];
    updateChartData(currentYear, currentMonthIndex + 1);
    updateDoughnutChart1();
}
updateDoughnutChart1();











const ctxDoughnut2 = document.getElementById('doughnutChart2').getContext('2d');



const barangayLabels = usernames;


const doughnutChart2 = new Chart(ctxDoughnut2, {
    type: 'bar',
    data: {
        labels: barangayLabels,
        datasets: [{
            label: 'Yearly Fire Alert per Barangay',
            data: [],
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 60,
                    minRotation: 60,
                    font: {
                        size: 13
                    }
                }
            }
        },
        plugins: {
            title: {
                display: true,
                text: `Yearly Fire Alert per Barangay - ${currentYear}`
            }
        }
    }
});

function updateDoughnutChart2() {
    fetch(`fetch_byearly.php?year=${currentYear}`)
        .then(response => response.json())
        .then(data => {
            doughnutChart2.data.datasets[0].data = barangayLabels.map(label => data[label] || 0);
            doughnutChart2.options.plugins.title.text = `Yearly Fire Alert per Barangay - ${currentYear}`;
            doughnutChart2.update();
        })
        .catch(error => console.error('Error fetching data:', error));
}

function prevYearlyBarangay() {
    currentYear--;
    updateDoughnutChart2();
}

function nextYearlyBarangay() {
    currentYear++;
    updateDoughnutChart2();
}

// Initial fetch and chart update
updateDoughnutChart2();








       
       




// Line Chart Setup
const ctxLine = document.getElementById('lineChart').getContext('2d');
let lineChart;
const categories = <?php echo json_encode($categories); ?>;


async function fetchYearlyFireAlertsData(year) {
    try {
        const response = await fetch(`fetch_data_involve.php?year=${year}`);
        if (!response.ok) {
            throw new Error(`Error fetching data: ${response.statusText}`);
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching fire alerts data:', error);
        return [];
    }
}


function initializeLineChart(year) {
    fetchYearlyFireAlertsData(year).then(data => {
        const labels = categories;


        const counts = categories.map(category => {
            const found = data.find(item => item.type === category);
            return found ? found.count : 0;
        });


        lineChart = new Chart(ctxLine, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: `${year} Fire Alerts`,
                    data: counts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                    }
                }
            }
        });
    }).catch(error => {
        console.error('Error initializing line chart:', error);
    });
}




function updateLineChart(year) {
    fetchYearlyFireAlertsData(year).then(data => {
        lineChart.data.datasets[0].label = `${year} Fire Alerts`;


        lineChart.data.labels = categories;


        const newData = categories.map(category => {
            const found = data.find(item => item.type === category);
            return found ? found.count : 0;
        });


        lineChart.data.datasets[0].data = newData;


        lineChart.update();


        if (data.length === 0) {
            console.error('No data available for the selected year.');
        }
    }).catch(error => {
        console.error('Error updating line chart:', error);
    });
}






initializeLineChart(currentYear);


function prevYearlyInvolved() {
    currentYear--;
    updateLineChart(currentYear);
}


function nextYearlyInvolved() {
    currentYear++;
    updateLineChart(currentYear);
}




       








        // CONNECTED FROM THE BUTTON FUNCTIONALITIY
        function generateLabels(year) {
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            return months.map(month => `${month} ${year}`);
        }


</script>


<!-- Barangay Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <div class="fire-truck-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="alert-icon">
            <h2 id="modalTitle">New Barangay Report Alert</h2>
            <span class="close1" onclick="closeModal()">✖</span>
        </div>
        <hr>
        <div id="reportDetails">
            <p><strong>Caller:</strong> <span id="modalCaller">Unknown</span></p>
            <p><strong>Location:</strong> <span id="modalLocation">Unknown</span></p>
            <p><strong>Involve:</strong> <span id="modalInvolve">Unknown</span></p>
            <p><strong>Status:</strong> <span id="modalStatus">Unknown</span></p>
        </div>
        <div class="button-container">
            <button onclick="redirectToReport()" class="view-report">View Report</button>
        </div>
    </div>
</div>

<!-- Mobile Report Modal -->
<div id="mobileReportModal" class="modal">
    <div class="modal-content">
        <div class="fire-truck-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="alert-icon">
            <h2 id="mobileModalTitle">New Mobile Report Alert</h2>
            <span class="close1" onclick="closeMobileModal()">✖</span>
        </div>
        <hr>
        <div id="mobileReportDetails">
            <p><strong>Caller:</strong> <span id="mobileModalCaller">Unknown</span></p>
            <p><strong>Location:</strong> <span id="mobileModalLocation">Unknown</span></p>
            <p><strong>Involve:</strong> <span id="mobileModalInvolve">Unknown</span></p>
            <p><strong>Status:</strong> <span id="mobileModalStatus">Unknown</span></p>
        </div>
        <div class="button-container">
            <button onclick="redirectToMobileReport()" class="view-report">View Report</button>
        </div>
    </div>
</div>


<style>
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%;
    overflow: auto; 
    background-color: rgba(0, 0, 0, 0.4); 
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto; 
    padding: 20px;
    border: 1px solid #888;
    width: 80%; 
    max-width: 600px; 
    border-radius: 10px; 
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
    margin-top: 310px;
}

.fire-truck-popup-header {
  display: flex;
  align-items: center; 
  justify-content: flex-start; 
  margin-bottom: 3px;
}

.alert-icon {
    width: 35px; 
    height: 35px; 
    margin-right: 10px;
    margin-top: 8px;
}

.fire-truck-popup-header h2 {
  margin-top: 20px; 
  font-size: 23px; 
  color: #333; 
  margin-bottom: 5px;
  font-weight: bold;
}

.close1 {
  margin-left: auto; 
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
  margin-top: -23px;
}

.close1:hover,
.close1:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

#reportDetails {
    margin-top: 10px;
    margin-bottom: 20px; 
    padding: 8px;
}

#reportDetails p {
    line-height: 2; 
    margin: 0; 
}

#reportDetails strong {
    color: #333; 
}

.button-container {
    display: flex;
    justify-content: flex-end; 
    margin-top: 20px; 
}

.view-report {
    background-color: #062B82;
    padding: 10px 20px; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
    font-size: 1em; 
    color: white;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #0056b3;
}
</style>

        
<!-- Audio for alert sound -->
<audio id="alertSound" src="https://pasigdrrmo.site/sounds/notif.mp3" preload="auto"></audio>
<audio id="alertMSound" src="https://pasigdrrmo.site/sounds/notif.mp3" preload="auto"></audio>

<script>
    var previousCount = null, previousMCount = null;
    var firstLoad = true, firstMLoad = true;
    var lastBarangayReportURL = null, lastMobileReportURL = null;

    function initializeAudio() {
        document.getElementById('alertSound').volume = 1.0;
        document.getElementById('alertMSound').volume = 1.0;
    }

    function fetchReportCount() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_report_count.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var currentCount = response.count;
                lastBarangayReportURL = response.lastReportURL;

                if (firstLoad) {
                    previousCount = currentCount;
                    firstLoad = false;
                    return;
                }

                if (currentCount > previousCount) {
                    var sound = document.getElementById('alertSound');
                    sound.currentTime = 0;
                    sound.play().then(() => {
                        openModal(response.latestReportDetails);
                    }).catch(error => console.error("Error playing sound: ", error));
                }
                previousCount = currentCount;
            }
        };
        xhr.onerror = function() { console.error("Request failed."); };
        xhr.send();
    }

    function fetchMobileReportCount() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_Mobilereport_count.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var currentMCount = response.count;
                lastMobileReportURL = response.lastReportURL;

                if (firstMLoad) {
                    previousMCount = currentMCount;
                    firstMLoad = false;
                    return;
                }

                if (currentMCount > previousMCount) {
                    var sound = document.getElementById('alertMSound');
                    sound.currentTime = 0;
                    sound.play().then(() => {
                        openMobileModal(response.latestReportDetails);
                    }).catch(error => console.error("Error playing sound: ", error));
                }
                previousMCount = currentMCount;
            }
        };
        xhr.onerror = function() { console.error("Request failed."); };
        xhr.send();
    }

    function openModal(details) {
        document.getElementById('modalCaller').innerText = details.caller;
        document.getElementById('modalLocation').innerText = details.location;
        document.getElementById('modalInvolve').innerText = details.involve;
        document.getElementById('modalStatus').innerText = details.status;
        document.getElementById('reportModal').style.display = "block";
    }

    function closeModal() {
        document.getElementById('reportModal').style.display = "none";
    }

    function redirectToReport() {
        if (lastBarangayReportURL) window.location.href = lastBarangayReportURL;
        closeModal();
    }

    function openMobileModal(details) {
        document.getElementById('mobileModalCaller').innerText = details.caller;
        document.getElementById('mobileModalLocation').innerText = details.location;
        document.getElementById('mobileModalInvolve').innerText = details.involve;
        document.getElementById('mobileModalStatus').innerText = details.status;
        document.getElementById('mobileReportModal').style.display = "block";
    }

    function closeMobileModal() {
        document.getElementById('mobileReportModal').style.display = "none";
    }

    function redirectToMobileReport() {
        if (lastMobileReportURL) window.location.href = lastMobileReportURL;
        closeMobileModal();
    }

    initializeAudio();
    setInterval(fetchReportCount, 10000);
    setInterval(fetchMobileReportCount, 10000);
    fetchReportCount();
    fetchMobileReportCount();
</script>

<!-- Request Section [COPY PASTE TO!!!!!!!!!!!!!!!!!!!!!!!!!!!111] -->
<a href="C3_Requests.php" class="request-link" style="display:none;">
    <div class="request-container" style="display:none;">
        <div class="request-header">
            <h3>Requests</h3> 
        </div>
        <p class="request-number" id="requestCount">0</p>
        <img src="images/RequestIcon.png" alt="Request Icon" class="request-image">
    </div>
</a>

<!-- HANGGANG DITO LANG!@!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! -->

<!-- REQUEST ALERT POPUP [HANGGANG DITO!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1] -->
<div id="requestModal" class="modal">
    <div class="modal-content">
        <div class="request-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="alert-icon">
            <h2 id="requestModalTitle">New Request Alert</h2>
            <span class="close2" onclick="closeRequestModal()"> ✖ </span>
        </div> 
        <hr>
        <div id="requestDetails">
            <p><strong>Responder:</strong> <span id="modalResponder">Unknown</span></p>
            <p><strong>Barangay:</strong> <span id="modalBarangay">Unknown</span></p>
            <p><strong>Request:</strong> <span id="modalRequest">Unknown</span></p>
            <p><strong>Status:</strong> <span id="modalRequestStatus">Unknown</span></p>
        </div>
        <div class="button-container">
            <button onclick="redirectToRequest()" class="view-request">View Request</button>
        </div>
    </div>
</div>

<audio id="requestAlertSound" src="https://pasigdrrmo.site/sounds/notif.mp3" preload="auto"></audio>

<style>
    .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    margin-top: 310px;
}

.request-popup-header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: 3px;
}

.alert-icon {
    width: 35px;
    height: 35px;
    margin-right: 10px;
    margin-top: 8px;
}

.request-popup-header h2 {
    margin-top: 20px;
    font-size: 23px;
    color: #333;
    margin-bottom: 5px;
    font-weight: bold;
}

.close2 {
    margin-left: auto;
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    margin-top: -23px;
}

.close2:hover,
.close2:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

#requestDetails {
    margin-top: 10px;
    margin-bottom: 20px;
    padding: 8px;
}

#requestDetails p {
    line-height: 2;
    margin: 0;
}

#requestDetails strong {
    color: #333;
}

.button-container {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.view-request {
    background-color: #062B82;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    color: white;
    transition: background-color 0.3s;
}

.view-request:hover {
    background-color: #0056b3;
}
</style>

<script>
    var previousRequestCount = null;
var firstRequestLoad = true;
var lastRequestURL = null; 

function initializeRequestAudio() {
    var sound = document.getElementById('requestAlertSound');
    sound.volume = 1.0; 
}

function fetchRequestCount() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_request_count.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            var currentRequestCount = response.count;
            lastRequestURL = response.lastRequestURL;

            document.getElementById('requestCount').innerText = currentRequestCount;

            if (firstRequestLoad) {
                previousRequestCount = currentRequestCount;
                firstRequestLoad = false;
                return;
            }

            if (currentRequestCount > previousRequestCount) {
                var sound = document.getElementById('requestAlertSound');
                sound.currentTime = 0;
            
                sound.play().then(() => {
                    var latestRequestDetails = response.latestRequestDetails;
            
                    openRequestModal(latestRequestDetails); 
                }).catch(error => {
                    console.error("Error playing sound: ", error);
                    alert("Error playing sound: " + error.message);
                });
            }

            previousRequestCount = currentRequestCount;
        }
    };

    xhr.onerror = function() {
        console.error("Request failed.");
    };

    xhr.send();
}

function openRequestModal(details) {
    document.getElementById('modalResponder').innerText = details.responder;
    document.getElementById('modalBarangay').innerText = details.barangay;
    document.getElementById('modalRequest').innerText = details.request;
    document.getElementById('modalRequestStatus').innerText = details.status;
    document.getElementById('requestModal').style.display = "block";
}

function closeRequestModal() {
    document.getElementById('requestModal').style.display = "none";
}

function redirectToRequest() {
    window.location.href = "C3_RequestSBar.php";
    closeRequestModal(); 
}

initializeRequestAudio();
setInterval(fetchRequestCount, 10000);
fetchRequestCount();

</script>

<!-- END DITOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1 -->







</body>
</html>





