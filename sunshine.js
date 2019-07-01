var sessionId = localStorage.getItem("sessionId");
var navItems = document.querySelector("header table tr");
if (sessionId === null) {
    navItems.innerHTML = `<td>
        <a href="?page=login" id="login_page" title="Log In"><i class="bx bx-star" aria-label="Log In"></i></a>
    </td>
    <td>
        <a href="?page=register" id="register_page" title="Register"><i class="bx bx-star" aria-label="Register"></i></a>
    </td>`;
} else {
    navItems.innerHTML = `<td>
        <a href="?page=feed" id="feed_page" title="Feed"><i class="bx bx-sun" aria-label="Feed"></i></a>
    </td>
    <td>
        <a href="?page=profile" id="profile_page" title="Profile"><i class="bx bx-user-circle" aria-label="Profile"></i></a>
    </td>
    <td>
        <a href="?page=connect" id="connect_page" title="Connect"><i class="bx bxs-yin-yang" aria-label="Connect"></i></a>
    </td>
    <td>
        <a href="?page=notifications" id="notifications_page" title="Notifications"><i class="bx bx-bell bx-tada" aria-label="Settings"></i></a>
    </td>`;
}
function logout() {
    localStorage.removeItem("sessionId");
    window.location = "http://192.168.254.56/sunshine.html?page=login";
}
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
var urlParams = new URLSearchParams(window.location.search);
var page = urlParams.get("page");
var pageContent = document.querySelector("#page");
if (page === "login" && sessionId === null || urlParams.has("page") === false && sessionId === null) {
    document.title = "Sunshine / Log In";
    var element = document.querySelector("#login_page");
    element.classList.add("active");
    pageContent.innerHTML = `<center><h2>Log In</h2></center>
    <form action="http://192.168.254.56" method="POST">
        <input type="hidden" name="action" value="login">
        <label for="login_identifier">Username or Email</label>
        <input type="username" name="identifier" placeholder="Username or Email" id="login_identifier">
        <label for="login_password">Password</label>
		<input type="password" name="password" placeholder="Password" id="login_password">
		<center><button type="submit">Log In</button></center>
    </form>`;
    var form = document.querySelector("form");
    form.onsubmit = function (e) {
        var formData = new FormData(form);
        fetch("http://192.168.254.56", {
            method: "POST",
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(myJson) {
            if (myJson.error !== false) {
                alert(myJson.error);
            } else {
                localStorage.setItem("sessionId", myJson.data);
                window.location = "http://192.168.254.56/sunshine.html?page=profile";
            }
        });
        e.preventDefault();
    }
} else if (page === "register" && sessionId === null) {
    document.title = "Sunshine / Register";
    var element = document.querySelector("#register_page");
    element.classList.add("active");
    pageContent.innerHTML = `<center><h2>Register</h2></center>
    <form action="http://192.168.254.56" method="POST">
        <input type="hidden" name="action" value="register">
        <label for="register_username">Username</label>
        <input type="username" name="username" placeholder="Username" id="register_username">
        <label for="register_email">Email</label>
        <input type="email" name="email" placeholder="Email" id="register_email">
        <label for="register_password">Password</label>
		<input type="password" name="password" placeholder="Password" id="register_password">
		<center><button type="submit">Register</button></center>
    </form>`;
    var form = document.querySelector("form");
    form.onsubmit = function (e) {
        var formData = new FormData(form);
        fetch("http://192.168.254.56", {
            method: "POST",
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(myJson) {
            if (myJson.error !== false) {
                alert(myJson.error);
            } else {
                localStorage.setItem("sessionId", myJson.data);
                window.location = "http://192.168.254.56/sunshine.html?page=profile";
            }
        });
        e.preventDefault();
    }
} else if (urlParams.has("page") === false || page === "feed") {
    document.title = "Sunshine / Feed";
    var element = document.querySelector("#feed_page");
    element.classList.add("active");
    var caughtUp,
    globalStatusId;
    function loadStatus(action) {
        pageContent.innerHTML = "";
        pageContent.style.background = "#ffffff";
        var formData = new FormData();
        formData.append("action", "feed");
        formData.append("session_id", sessionId);
        fetch("http://192.168.254.56", {
            method: "POST",
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(myJson) {
            if (myJson.data === false) {
                caughtUp = true;
                pageContent.innerHTML = `<div class="center"><h2 class="big-icon"><i class="bx bx-sun bx-spin"></i></h2>
                <h2>You're all caught up!</h2>
                <h3>There's no more statuses to display right now.</h3></div>`;
            } else {
                caughtUp = false;
                var formData2 = new FormData();
                formData2.append("action", "status-info");
                formData2.append("status_id", myJson.data.status_id);
                fetch("http://192.168.254.56", {
                    method: "POST",
                    body: formData2
                })
                .then(function(response2) {
                    return response2.json();
                })
                .then(function(myJson2) {
                    pageContent.innerHTML = `<center><h2><a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></h2><h2>${myJson2.data.username} <img src="https://img.icons8.com/material-rounded/100/0098ff/verified-account.png" alt="verified user" class="verified"></a></h2></center>
                    <h3>${myJson2.data.status}</h3>
                    <div class="card-actions">
                        <table>
                            <tr>
                                <td>
                                    <a onclick="interactWithStatus('${myJson.data.status_id}', 'add-like');" class="action-button" title="Like"><i class="bx bx-heart" aria-label="Like"></i></a>
                                </td>
                                <td>
                                    <a onclick="interactWithStatus('${myJson.data.status_id}', 'comment');" class="action-button comment" title="Comment"><i class="bx bx-comment-dots" aria-label="Comment"></i></a>
                                </td>
                                <td>
                                    <a onclick="interactWithStatus('${myJson.data.status_id}', 'skip');" class="action-button next" title="Skip"><i class="bx bx-right-arrow-alt" aria-label="Skip"></i></a>
                                </td>
                            </tr>
                        </table>
                    </div>`;
                    globalStatusId = myJson.data.status_id;
                    if (navigator.userAgent.match(/(iPod|iPhone|iPad|Mac)/i)) {
						// iOS
					} else {
						twemoji.size = '72x72';
  						twemoji.parse(document.body);
  					}
                });
            }
        });
    }
    function interactWithStatus(id, action) {
        var formData = new FormData();
        formData.append("action", action);
        formData.append("session_id", sessionId);
        formData.append("status_id", id);
        fetch("http://192.168.254.56", {
            method: "POST",
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(myJson) {
            if (myJson.error !== false) {
                alert(myJson.error);
            } else {
                if (action === "add-like") {
                    pageContent.style.background = "#ff0018";
                    pageContent.innerHTML = `<div class="center"><h2 class="big-icon-inversed"><i class="bx bx-heart"></i></h2></div>`;
                    //alert("You liked this status!");
                } else if (action === "skip") {
                    pageContent.style.background = "#00ffe7";
                    pageContent.innerHTML = `<div class="center"><h2 class="big-icon-inversed"><i class="bx bx-right-arrow-alt"></i></h2></div>`;
                    //alert("You skipped this status!");
                } else if (action === "comment") {
                    pageContent.style.background = "#00ff67";
                    pageContent.innerHTML = `<div class="center"><h2 class="big-icon-inversed"><i class="bx bx-comment-dots"></i></h2></div>`;
                } else {
                    //
                }
                sleep(1000).then(() => {
                    loadStatus();
                });
            }
        });
    }
    var mc = new Hammer(pageContent);
    mc.on("swipeleft", function(ev) {
        if (caughtUp !== true) {
            interactWithStatus(globalStatusId, "add-like");
        }
    });
    mc.on("swiperight", function(ev) {
        if (caughtUp !== true) {
            sleep(1000).then(() => {
                interactWithStatus(globalStatusId, "skip");
                loadStatus();
            });
        }
    });
    loadStatus();
} else if (page === "profile") {
    document.title = "Sunshine / Profile";
    var element = document.querySelector("#profile_page");
    element.classList.add("active");
    pageContent.innerHTML = `<h2>Profile</h2>
    <div class="grid">
        <div class="section">
            <div id="profile">
                <div class="profile_header"></div>
                <div class="profile_picture">
                    <div id="mood">😊</div>
                </div>
                <a class="profile_edit_button"><i class="bx bx-cog"></i></a>
                <div class="profile_stats">
                    <table>
                        <tr>
                            <td id="info_username"></td>
                            <td id="info_followers_count"></td>
                            <td id="info_following_count"></td>
                            <td id="info_posts_count"></td>
                        </tr>
                    </table>
                </div>
            </div>
            <a onclick="logout();" class="logout_button"><i class="bx bx-power-off"></i> Log Out</a>
        </div>
        <div class="section">
            <div id="profile_compose">
                <form action="http://localhost" method="POST">
                    <input type="hidden" name="action" value="new-status">
                    <input type="hidden" name="session_id" value="">
                    <textarea name="status" placeholder="What would you like to share?"></textarea>
                    <button type="submit" title="Share"><i class="bx bx-broadcast" aria-label="Share"></i></button>
                    <span id="characterCounter">1,000</span> <input type="text" name="mood" id="profile_compose_mood" placeholder=":)">
                </form>
            </div>
            <h2>Posts</h2>
            <div id="posts"></div>
        </div>
    </div>`;
    if (navigator.userAgent.match(/(iPod|iPhone|iPad|Mac)/i)) {
        // iOS
    } else {
        twemoji.size = "72x72";
        twemoji.parse(document.body);
    }
    function loadProfile() {
        var formData = new FormData();
        formData.append("action", "profile");
        formData.append("session_id", sessionId);
        fetch("http://192.168.254.56", {
            method: "POST",
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(myJson) {
            var usernameString = myJson.data.info.username;
            if (myJson.data.info.verified === true) {
                usernameString += ` <img src="https://img.icons8.com/material-rounded/100/0098ff/verified-account.png" alt="verified user" class="verified">`;
            }
            document.querySelector(".profile_stats #info_username").innerHTML = `${usernameString}`;
            document.querySelector(".profile_stats #info_followers_count").innerHTML = `${myJson.data.stats.followers}<br>followers`;
            document.querySelector(".profile_stats #info_following_count").innerHTML = `${myJson.data.stats.following}<br>following`;
            document.querySelector(".profile_stats #info_posts_count").innerHTML = `${myJson.data.stats.posts}<br>posts`;
        });
    }
    loadProfile();
    function loadPosts() {
        var formData = new FormData();
        formData.append("action", "show-statuses");
        formData.append("session_id", sessionId);
        fetch("http://192.168.254.56", {
            method: "POST",
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(myJson) {
            var array = myJson.data;
            var posts = document.querySelector("#posts");
            array.forEach(function (element) {
                posts.insertAdjacentHTML("beforeend", `<div class="post">
                    <h3>${element.status}</h3>
                    <div class="post_actions">
                        <table>
                            <tr>
                                <td>
                                    <a onclick="" class="action-button" title="Like"><i class="bx bx-heart" aria-label="Like"></i></a><span class="post_metric">${element.likes}</span>
                                </td>
                                <td>
                                    <a onclick="" class="action-button comment" title="Comment"><i class="bx bx-comment-dots" aria-label="Comment"></i></a><span class="post_metric">0</span>
                                </td>
                                <td>
                                    <a onclick="" class="action-button next" title="Delete"><i class="bx bx-trash" aria-label="Delete"></i></a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>`);
            });
        });
    }
    loadPosts();
    var tone;
    function checkTone() {
        tone = "loading";
        var status = document.querySelector("#profile_compose textarea").value;
        document.querySelector("#profile_compose form button").innerHTML = '<i class="bx bx-loader-circle bx-spin"></i>';
        sleep(1000).then(() => {
            var formData = new FormData();
            formData.append("action", "check-tone");
            formData.append("string", status);
            fetch("http://192.168.254.56", {
                method: "POST",
                body: formData,
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(myJson) {
                if (myJson.data.matches !== false && myJson.data.tone === "angry") {
                    tone = "angry";
                    document.querySelector("#profile_compose_mood").value = '😠';
                } else if (myJson.data.matches !== false && myJson.data.tone === "sad") {
                    tone = "sad";
                    document.querySelector("#profile_compose_mood").value = '😢';
                } else {
                    tone = "unknown";
                    document.querySelector("#profile_compose_mood").value = '😊';
                }
                document.querySelector("#profile_compose form button").innerHTML = '<i class="bx bx-broadcast" aria-label="Share"></i>';
                if (navigator.userAgent.match(/(iPod|iPhone|iPad|Mac)/i)) {
                    // iOS
                } else {
                    twemoji.size = '72x72';
                    twemoji.parse(document.body);
                }
            });
        });
    }
    var typingTimer;
    var doneTypingInterval = 1000;
    var myInput = document.querySelector("#profile_compose textarea");
    myInput.onkeyup = function(e) {
        clearTimeout(typingTimer);
        if (myInput.value === "") {
            tone = "unknown";
            document.querySelector("#profile_compose form button").innerHTML = '<i class="bx bx-broadcast" aria-label="Share"></i>';
        } else if (myInput.value) {
            typingTimer = setTimeout(doneTyping, doneTypingInterval);
        } else {
            // ... hi there
        }
        document.getElementById("characterCounter").innerHTML = (1000 - this.value.length).toLocaleString();
    }
    function doneTyping () {
        checkTone();
    }
    document.querySelector("#profile_compose form").onsubmit = function(e) {
        if (tone === "loading") {
            alert("Please wait for your status update to be analyzed.");
        } else if (tone === "angry") {
            var r = confirm("Are you sure you want to share this status? It's been detected as being potentially angry in nature. We won't stop you from sharing this, but if you need a minute to calm down hit cancel.");
            if (r == true) {
                // they hit OK
            }
        } else if (tone === "sad") {
            var r = confirm("Are you okay? Would you like to cancel this status and reach out to a friend? If you post this status we will notify a couple of your mutuals so they can get in touch with you. You'll get through this there is people out there who care about you!");
            if (r == true) {
                // they hit OK
            }
        } else {
            // just send
        }
        e.preventDefault();
    }
} else if (page === "connect") {
    document.title = "Sunshine / Connect";
    var element = document.querySelector("#connect_page");
    element.classList.add("active");
    pageContent.innerHTML = `<h2>Connect</h2>
<div id="connect_following_list">
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
    <a href="#"><div id="status_info"><img src="https://pbs.twimg.com/profile_images/1141207439620132865/YC2qbdfK_400x400.jpg" class="pp"><div id="mood">😊</div></div></a>
</div>`;
if (navigator.userAgent.match(/(iPod|iPhone|iPad|Mac)/i)) {
    // iOS
} else {
    twemoji.size = '72x72';
    twemoji.parse(document.body);
}
} else if (page === "notifications") {
    document.title = "Sunshine / Notifications";
    var element = document.querySelector("#notifications_page");
    element.classList.add("active");
    pageContent.innerHTML = `<h2>Notifications</h2>`;
} else {
    document.title = "Sunshine / Not Found";
    pageContent.innerHTML = `<div class="center">
        <h2 class="big-icon"><i class="bx bx-error"></i></h2>
        <h2>Not Found</h2>
        <h3>This page doesn't exist.</h3>
    </div>`;
}