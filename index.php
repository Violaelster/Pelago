<?php include __DIR__ . '/components/header.html'; ?>
<main>
    <section id="hero-index">
        <div id="video-section">
            <video id="video-hero" autoplay muted loop playsinline>
                <source src="/assets/images/snoop-hero.mp4" type="video/mp4">
                Din webbläsare stöder inte HTML5-video.
            </video>
            <div class="video-content">
                <div class="video-text">
                    <p class="video-small">Welcome To The Luxury Of The</p>
                    <h1 class="video-large"> D-O-Double-G's</h1>
                </div>

                <div class="video-buttons">
                    <a href="#" class="video-button">Book Now</a>
                    <a href="#" class="video-button">Learn More</a>
                </div>
            </div>
        </div>

        <section id="welcoming">
            <p>Welcome to Smooth Oasis, where every stay is as effortless as a Snoop Dogg beat. Experience the perfect blend of relaxation and style inspired by the Doggfather himself.</p>

            <p>Unwind in G-funk-inspired suites or sip signature cocktails at the Gin & Juice Lounge. Smooth Oasis is more than a place to stay—it's a lifestyle.</p>

            <p>Ready to live smooth? Book your stay now and join the vibe.</p>

        </section>

        <article id="spot">
            <p>
                Get yo spot
                <button class="spot-button">here</button>
            </p>
        </article>
    </section>

    <section id="highlights">
        <h2>Platinum Experiences</h2>
        <p>Step into the world of laid-back luxury. From smooth vibes to first-class indulgence, our handpicked activities let you live the high life—Snoop style. Tap to explore and make every moment a hit.</p>
        <article id="shortcuts">
            <div class="shortcut">
                <img src="/assets/svg/the-bling-shop.svg" alt="Gin & Juice Lounge" class="svg-shortcut">
            </div>

            <div class="shortcut">
                <img src="/assets/svg/gin-and-juice.svg" alt="Gin & Juice Lounge" class="svg-shortcut">
            </div>
            <div class="shortcut">
                <img src="/assets/svg/g-funk-nights.svg" alt="G-funk nights" class="svg-shortcut">
            </div>

            <div class="shortcut">
                <img src="/assets/svg/marthas-kitchen.svg" alt="Martha's Kitchen" class="svg-shortcut">
            </div>
        </article>
    </section>
    </section>
</main>
<script src="/public/js/index.js"></script>

<?php include __DIR__ . '/components/footer.php'; ?>