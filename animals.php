<?php include 'header.php'; ?>

<section id="animal-listings" style="padding: 2rem 0;">
    <div class="container">
        <h2 style="text-align: center;">Find Your New Best Friend</h2>
        
        <form id="filter-form" style="display: flex; justify-content: center; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
            <div class="form-group">
                <label for="type">Animal Type</label>
                <select id="type" name="type">
                    <option value="">All</option>
                    <option value="dog">Dog</option>
                    <option value="cat">Cat</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="age">Age</label>
                <select id="age" name="age">
                    <option value="">All</option>
                    <option value="puppy">Puppy/Kitten</option>
                    <option value="young">Young</option>
                    <option value="adult">Adult</option>
                    <option value="senior">Senior</option>
                </select>
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" placeholder="Enter city or zip code">
            </div>
            <button type="submit" class="btn" style="align-self: end;">Filter</button>
        </form>

        <div class="card-grid">
            <!-- Placeholder Animal 1 -->
            <div class="card">
                <img src="https://placedog.net/300/202" alt="Cute dog">
                <div class="card-content">
                    <h3>Rex</h3>
                    <p>Playful and energetic. Great with kids.</p>
                    <a href="animal-detail.php" class="btn">View Details</a>
                </div>
            </div>
            <!-- Placeholder Animal 2 -->
            <div class="card">
                <img src="https://placekitten.com/300/202" alt="Cute kitten">
                <div class="card-content">
                    <h3>Misty</h3>
                    <p>A calm and affectionate companion.</p>
                    <a href="animal-detail.php" class="btn">View Details</a>
                </div>
            </div>
            <!-- Placeholder Animal 3 -->
            <div class="card">
                <img src="https://placekitten.com/300/203" alt="Another cute kitten">
                <div class="card-content">
                    <h3>Whiskers</h3>
                    <p>Loves to explore and play with toys.</p>
                    <a href="animal-detail.php" class="btn">View Details</a>
                </div>
            </div>
             <!-- Placeholder Animal 4 -->
             <div class="card">
                <img src="https://placedog.net/300/203" alt="Cute dog">
                <div class="card-content">
                    <h3>Goldie</h3>
                    <p>A loyal friend waiting for a home.</p>
                    <a href="animal-detail.php" class="btn">View Details</a>
                </div>
            </div>
            <!-- Placeholder Animal 5 -->
            <div class="card">
                <img src="https://placekitten.com/300/204" alt="Cute kitten">
                <div class="card-content">
                    <h3>Shadow</h3>
                    <p>Shy at first, but very loving.</p>
                    <a href="animal-detail.php" class="btn">View Details</a>
                </div>
            </div>
            <!-- Placeholder Animal 6 -->
            <div class="card">
                <img src="https://placedog.net/300/204" alt="Another cute dog">
                <div class="card-content">
                    <h3>Rocky</h3>
                    <p>Full of energy and loves to run.</p>
                    <a href="animal-detail.php" class="btn">View Details</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
