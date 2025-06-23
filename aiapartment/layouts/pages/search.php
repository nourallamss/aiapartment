<?php
require_once 'config.php';

// Handle search
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$results = [];

if (isset($_GET['submit']) || !empty($location) || !empty($searchTerm)) {
    $results = searchData($location, $searchTerm);
}

$locations = getLocations();
?>


    <div class="container">
        <div class="header">
            <h1>🔍 Data Explorer</h1>
            <p>Search the Forfree Database</p>
        </div>
        
        <div class="search-form">
            <form method="GET" action="?page=search">
                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Filter by Location:</label>
                        <select name="location" id="location">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo htmlspecialchars($loc); ?>" 
                                        <?php echo ($location === $loc) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="search">Search Term:</label>
                        <input type="text" name="search" id="search" 
                               value="<?php echo htmlspecialchars($searchTerm); ?>" 
                               placeholder="Search by name, description, phone, email...">
                    </div>
                </div>
                
                <button type="submit" name="submit" class="search-btn">
                    🔍 Search Database
                </button>
            </form>
        </div>
        
        <div class="results-section">
            <?php if (isset($_GET['submit']) || !empty($location) || !empty($searchTerm)): ?>
                <div class="results-header">
                    <h2>Search Results (<?php echo count($results); ?> found)</h2>
                    <?php if (!empty($location) || !empty($searchTerm)): ?>
                        <p>
                            <?php if (!empty($location)): ?>
                                Location: <strong><?php echo htmlspecialchars($location); ?></strong>
                            <?php endif; ?>
                            <?php if (!empty($location) && !empty($searchTerm)): ?> | <?php endif; ?>
                            <?php if (!empty($searchTerm)): ?>
                                Search: <strong><?php echo htmlspecialchars($searchTerm); ?></strong>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <?php if (count($results) > 0): ?>
                    <div class="results-grid">
                        <?php foreach ($results as $row): ?>
                            <div class="result-card">
                                <div class="location"><?php echo htmlspecialchars($row['location']); ?></div>
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                
                                <?php if (!empty($row['description'])): ?>
                                    <p><span class="label">Description:</span> <?php echo htmlspecialchars($row['description']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($row['phone'])): ?>
                                    <p><span class="label">Phone:</span> <?php echo htmlspecialchars($row['phone']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($row['email'])): ?>
                                    <p><span class="label">Email:</span> <?php echo htmlspecialchars($row['email']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($row['address'])): ?>
                                    <p><span class="label">Address:</span> <?php echo htmlspecialchars($row['address']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <h3>No Results Found</h3>
                        <p>Try adjusting your search criteria or location filter.</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-results">
                    <h3>Welcome to Data Explorer</h3>
                    <p>Use the search form above to find data in the forfree database.</p>
                    <p>You can filter by location or search across all fields.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
