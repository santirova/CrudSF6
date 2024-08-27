document.addEventListener('DOMContentLoaded', () => {
    const likeButtons = document.querySelectorAll('.like-button');

    likeButtons.forEach(button => {
        button.addEventListener('click', async () => {
            const postId = button.getAttribute('data-post-id');
            try {
                const response = await fetch(`http://localhost/CrudSymfony6/public/index.php/post/${postId}/like`, { method: 'POST' });
                if (response.ok) {
                    const data = await response.json();
                    console.log(data)
                    const likeCountElement = document.getElementById(`like-count-${postId}`);
                    const icon = button.querySelector('i');
                    if (data.status === "liked") {
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                        likeCountElement.textContent = parseInt(likeCountElement.textContent) + 1;
                    } else {
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                        likeCountElement.textContent = parseInt(likeCountElement.textContent) - 1;
                    }
                }
            } catch (error) {
                console.error('Error liking post:', error);
            }
        });
    });
});
