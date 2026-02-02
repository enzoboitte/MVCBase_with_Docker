function onUserUpdated(e, data) 
{
    if (data.code === 200)
        window.location.href = '/dashboard/users';
    else
        alert('Erreur lors de la mise à jour du profil');
}

function onUserCreated(e, data) 
{
    if (data.code === 201)
        window.location.href = '/dashboard/users';
    else
        alert('Erreur lors de la création du profil');
}