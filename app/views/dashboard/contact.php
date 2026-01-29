<link rel="stylesheet" href="/public/src/css/dashboard/contact.css">

<?
ob_start();
?>

<div class="dashboard">
    <h1><?= htmlspecialchars($title) ?></h1>

    <div class="list_message">
    </div>

    <div id="contact" class="popup">
        <div class="popup_content">
            <div class="popup_header">
                <h2 class="popup_title">Détails du message</h2>
                <span class="popup_close">&times;</span>
            </div>
            <div class="popup_body">
                <h4 id="subject"></h4>
                <pre id="content" class="content"></pre>
            </div>
        </div>
    </div>
</div>
<script>
    function clickBtnAction(e) {
        e.stopPropagation();

        console.log('Button action clicked:', e.target);
        const action = e.target.dataset.action;
        const messageId = e.target.parentElement.dataset.id;

        switch(action) {
            case 'delete':
                const confirmed = confirm('Are you sure you want to delete this message?');
                if (confirmed) {
                    apiRequest('DELETE', `/contact/${messageId}`).then(result => {
                        if (result.code === 200) {
                            loadMessage();
                        } else {
                            alert('Error deleting message: ' + result.message);
                        }
                    }).catch(error => {
                        console.error('Error deleting message:', error);
                    });
                }
                break;
            case 'archive':
            case 'favorite':
                apiRequest('PUT', `/contact/status/${messageId}/${action}`).then(result => {
                    if (result.code === 200) {
                        loadMessage();
                    } else {
                        alert('Error archiving message: ' + result.message);
                    }
                }).catch(error => {
                    console.error('Error archiving message:', error);
                });
                break;
        }
    }

    async function loadMessage() 
    {
        try {
            const result = await apiRequest('GET', '/contact');

            if (result.code === 200) {
                const messages = result.data;
                const listMessageDiv = document.querySelector('.list_message');
                listMessageDiv.innerHTML = '';

                messages.forEach(message => {
                    const messageCard = document.createElement('div');
                    messageCard.classList.add('message_card');
                    if(message.status == "new")
                        messageCard.classList.add('unread');
                    messageCard.addEventListener('click', () => {
                        const popupContent = document.getElementById('content');
                        popupContent.textContent = `${message.message}`;
                        const popupSubject = document.getElementById('subject');
                        popupSubject.textContent = `${message.subject}`;
                        openPopup('contact');
                    });

                    messageCard.innerHTML = `
                        <div class="message_header" data-id="${message.id}">
                            <span class="message_sender">${message.name}</span>
                            <span class="message_email">${message.email}</span>
                            <span class="message_date">${message.created_at}</span>
                            <span class="message_favorite ${message.pin == "favorite" ? 'favorite' : ''}" data-action="favorite"><i class="fa fa-star-o" style="pointer-events: none;"></i></span>
                            <span class="message_archive ${message.pin == "archived" ? 'archived' : ''}" data-action="archive"><i class="fa fa-archive" style="pointer-events: none;"></i></span>
                            <span class="message_delete" data-action="delete"><i class="fa fa-trash-o" style="pointer-events: none;"></i></span>
                        </div>
                        <div class="message_subject">${message.subject}</div>
                        <div class="message_body">
                            ${message.message}
                        </div>
                    `;

                    listMessageDiv.appendChild(messageCard);
                });
                
                const l_aMessage = document.querySelectorAll('.message_card span[data-action]');
                l_aMessage.forEach(element => {
                    console.log('Adding listener to', element);
                    element.addEventListener('click', clickBtnAction);
                });
            } else {
                console.error('Error loading messages:', result.message);
            }
        } catch (error) {
            console.error('Error fetching messages:', error);
        }    
    }
    document.addEventListener('DOMContentLoaded', () => {
        loadMessage();
    });
</script>
<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>