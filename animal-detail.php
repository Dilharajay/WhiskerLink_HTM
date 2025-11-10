<?php include 'header.php'; ?>

<style>
    .animal-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-top: 2rem;
        align-items: start;
    }
    .animal-photo img {
        width: 100%;
        border-radius: 5px;
    }
    .animal-info h2 {
        margin-top: 0;
    }
    .animal-info ul {
        list-style: none;
        padding: 0;
    }
    .animal-info ul li {
        padding: 5px 0;
    }
    .animal-info ul li strong {
        display: inline-block;
        width: 100px;
    }
    @media(max-width: 768px) {
        .animal-detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section id="animal-detail" style="padding: 2rem 0;">
    <div class="container">
        <div class="animal-detail-grid">
            <div class="animal-photo">
                <img src="https://placedog.net/500/500" alt="Cute dog">
            </div>
            <div class="animal-info">
                <h2>Buddy</h2>
                <p>A friendly and playful companion who loves cuddles and long walks in the park. Buddy is great with kids and other dogs. He is looking for a forever home with a loving family.</p>
                <ul>
                    <li><strong>Breed:</strong> Golden Retriever</li>
                    <li><strong>Age:</strong> 3 years</li>
                    <li><strong>Sex:</strong> Male</li>
                    <li><strong>Health:</strong> Vaccinated, Neutered</li>
                    <li><strong>Location:</strong> City Shelter</li>
                </ul>
                <?php if (isset($_SESSION['loggedin'])): ?>
                    <a href="#" class="btn btn-accent">Adopt Me</a>
                <?php else: ?>
                    <p><a href="login.php">Log in</a> to adopt.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
