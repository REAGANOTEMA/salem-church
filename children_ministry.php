<?php
require_once 'config.php';
require_once 'db_connection.php';

$pageTitle = "Children's Ministry - Salem Dominion Ministries";
$currentPage = 'children';

$childrenImages = [
    'assets/children-celebrating-Z18oVWUU.jpeg',
    'assets/children-with-books-Cc2LmxDu.jpeg',
    'assets/children-food-20X1VRUx.jpeg',
    'assets/children-eating-withpastor-Bagnofdx.jpeg',
    'assets/kids-supports-are-welcome.jpeg',
    'assets/numbers-ofkids-rejoicing-lifting-booksup-in-joy.jpeg',
    'assets/a-kid-showing-how-kindness-isgood-BBxs16el.jpeg',
    'assets/one-kid-in-joy.jpeg',
    'assets/gifting-achild-withbook-insmile.jpeg',
    'assets/generalpastor-with-kids-eating-together.jpeg',
];

include 'components/header.php';
?>

<style>
    .children-hero {
        background: linear-gradient(135deg, rgba(16,185,129,0.85) 0%, rgba(14,165,233,0.6) 100%), url('assets/children-celebrating-Z18oVWUU.jpeg');
        background-size: cover; background-position: center; min-height: 60vh;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .children-hero::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(to top, #fff, transparent); }
    .children-hero-content { position: relative; z-index: 2; text-align: center; color: #fff; padding: 0 20px; }
    .children-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2.5rem,6vw,3.5rem); font-weight: 900; }
    .children-hero p { font-size: 1.25rem; opacity: 0.95; }
    .section-gap { padding: 80px 0; }
    .section-gap.alt-bg { background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f0f9ff 100%); }
    .section-title-custom { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,3rem); font-weight: 700; color: #0f172a; text-align: center; margin-bottom: 0.5rem; }
    .section-title-custom::after { content: ''; display: block; width: 80px; height: 4px; background: linear-gradient(90deg, #10b981, #fbbf24); margin: 15px auto 0; border-radius: 2px; }
    .section-subtitle-custom { text-align: center; color: #64748b; font-size: 1.1rem; margin-bottom: 3rem; }
    .program-card { background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.4s ease; border-left: 4px solid #10b981; height: 100%; }
    .program-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .program-card h5 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 0.75rem; }
    .program-card p { color: #64748b; line-height: 1.7; font-size: 0.95rem; margin-bottom: 0; }
    .program-card .age-badge { display: inline-block; background: #10b98115; color: #10b981; padding: 3px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.75rem; }
    .age-group-card { background: #fff; border-radius: 16px; padding: 2rem; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.4s ease; height: 100%; }
    .age-group-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .age-group-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem; color: #fff; }
    .age-group-card h5 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 0.5rem; }
    .age-group-card p { color: #64748b; font-size: 0.9rem; line-height: 1.6; }
    .schedule-table { border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .schedule-table thead th { background: linear-gradient(135deg, #0f172a, #1e3a5f); color: #fff; border: none; padding: 1rem; font-weight: 600; }
    .schedule-table tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
    .schedule-table tbody tr:hover { background: #f0fdf4; }
    .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; }
    .gallery-item { border-radius: 12px; overflow: hidden; position: relative; aspect-ratio: 4/3; }
    .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
    .gallery-item:hover img { transform: scale(1.08); }
    .gallery-item::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, rgba(16,185,129,0.6), transparent); opacity: 0; transition: opacity 0.4s ease; }
    .gallery-item:hover::after { opacity: 1; }
    .cta-children { background: linear-gradient(135deg, #10b981, #0ea5e9); color: #fff; text-align: center; position: relative; overflow: hidden; }
    .cta-children::before { content: ''; position: absolute; top: 0; left: -100%; width: 300%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent); animation: shimmC 12s infinite; }
    @keyframes shimmC { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .cta-children h2 { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,2.8rem); margin-bottom: 1rem; position: relative; }
    .cta-children p { font-size: 1.15rem; opacity: 0.95; margin-bottom: 2rem; position: relative; }
    .cta-btn-green { display: inline-flex; align-items: center; gap: 10px; padding: 14px 36px; background: #fff; color: #10b981; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease; position: relative; font-size: 1.05rem; }
    .cta-btn-green:hover { background: #0f172a; color: #fff; transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
</style>

<section class="children-hero">
    <div class="children-hero-content" data-aos="fade-up" data-aos-duration="1200">
        <h1><i class="fas fa-child-reaching me-3"></i>Children's Ministry</h1>
        <p>Raising the next generation in the love and knowledge of God</p>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">About Our Children's Ministry</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Where faith, fun, and friends come together</p>
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                <div class="program-card" style="border-left-color: #10b981;">
                    <p style="color: #475569; line-height: 1.9; font-size: 1.05rem;">
                        At Salem Dominion Ministries, we believe children are not just the future of the church - they are the church of today. Our Children's Ministry provides a vibrant, safe, and exciting environment where children can encounter God, learn biblical truths, and develop a personal relationship with Jesus Christ.
                    </p>
                    <p style="color: #475569; line-height: 1.9; font-size: 1.05rem; margin-top: 1rem;">
                        Through creative storytelling, dynamic worship, hands-on activities, and age-appropriate Bible lessons, we help children understand God's love in ways that resonate with their hearts. Every child who walks through our doors is welcomed with open arms and shown the love of Christ.
                    </p>
                    <p style="color: #475569; line-height: 1.9; font-size: 1.05rem; margin-top: 1rem;">
                        Our trained and caring volunteers create a nurturing atmosphere where children feel valued, safe, and free to express their faith. From nursery to pre-teen, we have programs designed for every stage of a child's development.
                    </p>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                <img src="assets/children-with-books-Cc2LmxDu.jpeg" alt="Children learning" class="img-fluid" style="border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-height: 400px; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

<section class="section-gap alt-bg">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Age Groups</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Programs designed for every stage of childhood</p>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="age-group-card">
                    <div class="age-group-icon" style="background: linear-gradient(135deg, #f472b6, #ec4899);"><i class="fas fa-baby"></i></div>
                    <h5>Nursery</h5>
                    <p class="mb-1"><strong>0 - 2 Years</strong></p>
                    <p>Tender care with gentle worship and play-based learning</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="age-group-card">
                    <div class="age-group-icon" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);"><i class="fas fa-child"></i></div>
                    <h5>Toddlers</h5>
                    <p class="mb-1"><strong>3 - 5 Years</strong></p>
                    <p>Bible stories, songs, crafts, and interactive play</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="age-group-card">
                    <div class="age-group-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-book-open"></i></div>
                    <h5>Primary</h5>
                    <p class="mb-1"><strong>6 - 9 Years</strong></p>
                    <p>Foundational Bible lessons, memory verses, and team activities</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="age-group-card">
                    <div class="age-group-icon" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);"><i class="fas fa-graduation-cap"></i></div>
                    <h5>Pre-Teens</h5>
                    <p class="mb-1"><strong>10 - 12 Years</strong></p>
                    <p>Deepening faith, leadership skills, and peer fellowship</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Programs & Activities</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Engaging, educational, and spirit-filled experiences</p>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="program-card">
                    <span class="age-badge"><i class="fas fa-bible me-1"></i> Sunday School</span>
                    <h5>Bible Study & Lessons</h5>
                    <p>Interactive Bible lessons tailored to each age group with visual aids, role plays, and storytelling to make God's Word come alive for children.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="program-card">
                    <span class="age-badge"><i class="fas fa-music me-1"></i> Worship</span>
                    <h5>Kids Worship</h5>
                    <p>Energetic, age-appropriate worship sessions where children learn to praise and worship God through singing, dancing, and movement.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="program-card">
                    <span class="age-badge"><i class="fas fa-palette me-1"></i> Creative</span>
                    <h5>Arts & Crafts</h5>
                    <p>Creative activities that reinforce biblical lessons through drawing, painting, building, and other hands-on projects that nurture creativity.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="program-card">
                    <span class="age-badge"><i class="fas fa-futbol me-1"></i> Games</span>
                    <h5>Sports & Games</h5>
                    <p>Fun-filled games and activities that teach teamwork, fair play, and cooperation while keeping children active and engaged.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="program-card">
                    <span class="age-badge"><i class="fas fa-masks-theater me-1"></i> Events</span>
                    <h5>Special Events</h5>
                    <p>Annual children's conferences, holiday programs, talent shows, and special celebrations that create lasting memories.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="program-card">
                    <span class="age-badge"><i class="fas fa-hand-holding-heart me-1"></i> Outreach</span>
                    <h5>Community Service</h5>
                    <p>Teaching children the value of compassion through community outreach, charity drives, and helping those in need.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-gap alt-bg">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Weekly Schedule</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Join us for these exciting children's programs</p>
        <div data-aos="fade-up" data-aos-delay="200">
            <table class="table schedule-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar-day me-2"></i>Day</th>
                        <th><i class="fas fa-clock me-2"></i>Time</th>
                        <th><i class="fas fa-list me-2"></i>Activity</th>
                        <th><i class="fas fa-users me-2"></i>Age Group</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><strong>Sunday</strong></td><td>9:00 AM - 10:30 AM</td><td>Sunday School & Worship</td><td>All Ages</td></tr>
                    <tr><td><strong>Wednesday</strong></td><td>4:00 PM - 5:30 PM</td><td>Bible Study & Memory Verses</td><td>6 - 12 Years</td></tr>
                    <tr><td><strong>Friday</strong></td><td>4:00 PM - 5:30 PM</td><td>Creative Arts & Games</td><td>3 - 9 Years</td></tr>
                    <tr><td><strong>Saturday</strong></td><td>10:00 AM - 12:00 PM</td><td>Special Programs & Rehearsals</td><td>All Ages</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Photo Gallery</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Moments of joy, faith, and learning</p>
        <div class="gallery-grid" data-aos="fade-up" data-aos-delay="200">
            <?php foreach ($childrenImages as $img): ?>
            <div class="gallery-item">
                <img src="<?= htmlspecialchars($img) ?>" alt="Children's Ministry" loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-gap cta-children">
    <div class="container position-relative">
        <h2 data-aos="fade-up"><i class="fas fa-heart me-2"></i>Register Your Child Today</h2>
        <p data-aos="fade-up" data-aos-delay="100">Give your child the foundation of faith that will last a lifetime. Join our Children's Ministry family!</p>
        <div data-aos="fade-up" data-aos-delay="200">
            <a href="contact.php" class="cta-btn-green"><i class="fas fa-phone"></i> Contact Us to Register</a>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>
