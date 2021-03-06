"use strict";

// Search for posts

const resultContainer = document.querySelector(".postItems");
const searchBtn = document.querySelector(".searchButton2");
const input = document.querySelector(".searchPost");
const form = document.querySelector(".postForm");
input.value = "";


const createPostTemplate = (postId, postImage, username) => {
    return `<a href="/post.php?id=${postId}">
    <img src="${postImage}" class="postImages" alt="">
</a>
<p>
    <a class="userLink" href="/profile.php?username=${username}">
        ${username}
    </a>
</p>`
}


input.addEventListener("input", (e) => {

    e.preventDefault();

    resultContainer.innerHTML = "";
    const formData = new FormData(form);

    fetch("app/users/searchtest.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        return response.json();
    })
    .then(posts => {

        if (input.value.length < 2) {
            resultContainer.innerHTML = "";
        }

        posts.forEach(post => {

            if (e.target.value.length < 2) {
                resultContainer.innerHTML = "";
            }

            if (e.target.value.length > 2) {

                const postTemplate = createPostTemplate(post.id, post.post_image, post.username);
                const li = document.createElement("li");
                li.innerHTML = postTemplate;
                resultContainer.appendChild(li);
            }
        })
    })
})

