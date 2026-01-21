<?php
include 'header.php';
?>
<main>
    <section class="hero">
        <div class="hero-content">
            <h1>Elevate Your Events with EventPro</h1>
            <p>Professional event management solutions that transform your vision into unforgettable experiences</p>
            <div class="hero-buttons">
                <a href="registration.php" class="btn btn-primary">Start Planning Now</a>
                <a href="#services" class="btn btn-secondary">Explore Services</a>
            </div>
        </div>
    </section>

    <!-- serch filter -->
<section class="filter-section">
    <div class="container">
        <h2 class="section-title text-center">Find Events</h2>

        <!-- Search Filters -->
        <div class="filter-form">
            
            <input type="text" id="search" placeholder="Search event name..." onkeyup="loadEvents()">

            <select id="category" onchange="loadEvents()">
                <option value="">All Categories</option>
                <option value="Corporate">Corporate</option>
                <option value="Wedding">Wedding</option>
                <option value="Birthday">Birthday</option>
            </select>

            <input type="date" id="date" onchange="loadEvents()">

            <label class="checkbox">
                <input type="checkbox" id="sort" onchange="loadEvents()">
                Sort by Upcoming
            </label>
        </div>

        <!-- Event Result Container -->
        <div id="eventResults" class="row fade"></div>

    </div>
</section>

<script>
function loadEvents() {
    let search = document.getElementById('search').value;
    let category = document.getElementById('category').value;
    let date = document.getElementById('date').value;
    let sort = document.getElementById('sort').checked ? 1 : 0;

    let xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_events.php?search=" + search +
             "&category=" + category +
             "&date=" + date +
             "&sort=" + sort, true);

    xhr.onload = function () {
        document.getElementById('eventResults').innerHTML = this.responseText;
        animateCards();
    }
    xhr.send();
}

// Card animation
function animateCards() {
    const cards = document.querySelectorAll(".event-card");
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add("show");
        }, index * 150);
    });
}

document.addEventListener("DOMContentLoaded", loadEvents);
</script>


    <section id="services" class="section">
        <div class="container">
            <div class="section-title">
                <h2>Premium Event Services</h2>
                <p>Comprehensive solutions for every type of celebration and gathering</p>
            </div>
            
            <div class="row">
                <div class="col-4">
                    <div class="card fade-in">
                        <div class="card-header">
                            <h3><i class="fas fa-building"></i> Corporate Events</h3>
                        </div>
                        <div class="card-body">
                            <img src="image/corpo.jpg" alt="Corporate Events" class="service-image">
                            <p>Professional conferences, seminars, product launches, and corporate gatherings with meticulous planning and execution. Our team ensures every detail is taken care of, from venue selection to guest management.</p>
                            <ul class="service-features">
                                <li><i class="fas fa-check"></i> Venue Selection</li>
                                <li><i class="fas fa-check"></i> Audio-Visual Setup</li>
                                <li><i class="fas fa-check"></i> Catering Services</li>
                                <li><i class="fas fa-check"></i> Guest Management</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-4">
                    <div class="card fade-in">
                        <div class="card-header">
                            <h3><i class="fas fa-ring"></i> Weddings</h3>
                        </div>
                        <div class="card-body">
                            <img src="image/Wedding.jpg" alt="Weddings" class="service-image">
                            <p>Create your dream wedding with our expert planning, from intimate ceremonies to grand celebrations. We handle everything from theme design to vendor coordination, ensuring your special day is perfect.</p>
                            <ul class="service-features">
                                <li><i class="fas fa-check"></i> Theme Design</li>
                                <li><i class="fas fa-check"></i> Vendor Coordination</li>
                                <li><i class="fas fa-check"></i> Day-of Coordination</li>
                                <li><i class="fas fa-check"></i> Budget Management</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-4">
                    <div class="card fade-in">
                        <div class="card-header">
                            <h3><i class="fas fa-birthday-cake"></i> Birthdays</h3>
                        </div>
                        <div class="card-body">
                            <img src="image/bday.jpg" alt="Birthdays" class="service-image">
                            <p>Memorable birthday celebrations tailored to all ages, from children's parties to milestone anniversaries. Whether it's a child's party or a milestone celebration, we create unforgettable experiences that reflect your vision.</p>
                            <ul class="service-features">
                                <li><i class="fas fa-check"></i> Theme Parties</li>
                                <li><i class="fas fa-check"></i> Entertainment</li>
                                <li><i class="fas fa-check"></i> Decorations</li>
                                <li><i class="fas fa-check"></i> Catering Options</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Features Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose EventPro?</h2>
                <p>Experience the difference with our professional approach to event management</p>
            </div>
            
            <div class="row">
                <div class="col-3">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4>Premium Quality</h4>
                        <p>Exceptional service quality with attention to every detail</p>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>On-Time Delivery</h4>
                        <p>Punctual execution and seamless timeline management</p>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p>Round-the-clock customer support for all your needs</p>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Trusted Partner</h4>
                        <p>Reliable and trustworthy event management services</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Client Testimonials</h2>
                <p>Hear what our satisfied clients have to say about our services</p>
            </div>
            
            <div class="row">
                <div class="col-4">
                    <div class="testimonial-card">
                        <img src="images/IMG_6981.JPG" alt="Sarah Johnson" class="testimonial-image">
                        <div class="testimonial-content">
                            <p>"EventPro transformed our corporate conference into an unforgettable experience. Their attention to detail was exceptional!"</p>
                        </div>
                        <div class="testimonial-author">
                            <h5>Sarah Johnson</h5>
                            <p>CEO, Tech Innovations</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-4">
                    <div class="testimonial-card">
                        <img src="images/IMG_6984.JPG" alt="Michael & Emily" class="testimonial-image">
                        <div class="testimonial-content">
                            <p>"Our wedding day was perfect thanks to EventPro. They handled everything seamlessly and made our dreams come true."</p>
                        </div>
                        <div class="testimonial-author">
                            <h5>Michael & Emily</h5>
                            <p>Newlyweds</p>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="testimonial-card">
                        <img src="images/IMG_9871.JPG" alt="David Lee" class="testimonial-image">
                        <div class="testimonial-content">
                            <p>"EventPro made my 50th birthday celebration unforgettable. Their creativity and professionalism were outstanding."</p>
                        </div>
                        <div class="testimonial-author">
                            <h5>David Lee</h5>
                            <p>Entrepreneur</p>
                        </div>
                    </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content text-center">
                <h2>Ready to Create Your Perfect Event?</h2>
                <p>Get started today and let us bring your vision to life with our expert event management services</p>
                <div class="cta-buttons">
                    <a href="registration.php" class="btn btn-primary btn-large">Get Started Now</a>
                    <a href="contact.php" class="btn btn-secondary btn-large">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
include 'footer.php';
?>
