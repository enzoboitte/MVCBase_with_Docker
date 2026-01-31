// request the api REST for GET, POST, PUT, DELETE
async function apiRequest(method, endpoint, data = null) 
{
    const options = 
    {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    if (data)
        options.body = JSON.stringify(data);

    try 
    {
        const response = await fetch(endpoint, options);
        if (!response.ok)
            throw new Error(`HTTP error! status: ${response.status}`);
        return await response.json();
    } catch (error) 
    {
        console.error('Error during API request:', error);
        throw error;
    }
}

async function handleTable(table)
{
    const endpoint = table.getAttribute('data-api-endpoint');
    try 
    {
        const data = await apiRequest('GET', endpoint);
        const diploma = data.data;
        if(!diploma || diploma.length === 0) return;
        const thead = table.querySelector('thead tr');
        thead.innerHTML = '';
        
        // populate table header
        const listKeys = Object.keys(diploma[0]);

        // add key update/delete if not present
        if(!listKeys.includes('update'))
            listKeys.push('Up');
        if(!listKeys.includes('delete'))
            listKeys.push('Del');
        listKeys.forEach(key => 
        {
            const th = document.createElement('th');
            th.textContent = key.charAt(0).toUpperCase() + key.slice(1).replace('_', ' ');
            thead.appendChild(th);
        });
        
        // populate table with data
        const tbody = table.querySelector('tbody');
        tbody.innerHTML = '';
        diploma.forEach(item => 
        {
            const row = document.createElement('tr');
            for (const key in item) 
            {
                const cell = document.createElement('td');
                cell.textContent = item[key];
                row.appendChild(cell);
            }
            // add update button
            if(!item.hasOwnProperty('update'))
            {
                const updateCell = document.createElement('td');
                const updateButton = document.createElement('button');
                updateButton.classList.add('update-button');
                updateButton.textContent = 'Update';
                updateButton.addEventListener('click', () => 
                {
                    window.location.href = `/dashboard/diploma/edit/${item.id}`;
                });
                updateCell.appendChild(updateButton);
                row.appendChild(updateCell);
            }
            // add delete button
            if(!item.hasOwnProperty('delete'))
            {
                const deleteCell = document.createElement('td');
                const deleteButton = document.createElement('button');
                deleteButton.classList.add('delete-button');
                deleteButton.textContent = 'Delete';
                deleteButton.addEventListener('click', () => 
                {
                    if(confirm('Are you sure you want to delete this item?'))
                    {
                        apiRequest('DELETE', `${endpoint}/${item.id}`)
                        .then(() => 
                        {
                            row.remove();
                        })
                        .catch(error => 
                        {
                            console.error('Failed to delete item:', error);
                        });
                    }
                });
                deleteCell.appendChild(deleteButton);
                row.appendChild(deleteCell);
            }
            tbody.appendChild(row);
        });
    } catch (error) 
    {
        console.error('Failed to load table data:', error);
    }
}

function openPopup(id)
{
    const popup = document.getElementById(id);
    if(popup)
    {
        popup.classList.add('show');

        const closeBtn = popup.querySelector(`#${id} .popup_close`);
        if(closeBtn)
        {
            closeBtn.addEventListener('click', () => 
            {
                popup.classList.remove('show');
            });
        }

        // close popup when clicking outside content
        popup.addEventListener('click', (event) => 
        {
            if(event.target === popup)
            {
                popup.classList.remove('show');
            }
        });
    }
}

// a chaque chargement de la page, recupere tous les formulaires/tableaux avec l'attribut data-api-endpoint
document.addEventListener('DOMContentLoaded', () => 
{
    const forms = document.querySelectorAll('form[data-api-endpoint]');
    forms.forEach(form => 
    {
        form.addEventListener('submit', async (event) => 
        {
            event.preventDefault();
            const endpoint = form.getAttribute('data-api-endpoint');
            const method   = form.getAttribute('data-api-method') || 'POST';
            const call     = form.getAttribute('data-api-action') || '';
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => 
            {
                data[key] = value;
            });

            try 
            {
                const result = await apiRequest(method, endpoint, data);
                if(call)
                {
                    window[call](form, result);
                }
            } catch (error) 
            {
                console.error('API request failed:', error);
            }
        });
    });

    const formsUp = document.querySelectorAll('form[data-api-endpoint][data-api-method="PUT"]');
    formsUp.forEach(async form => 
    {
        const endpoint = form.getAttribute('data-api-endpoint');

        try 
        {
            const data = await apiRequest('GET', endpoint);
            const item = data.data;
            if(!item) return;
            const formData = new FormData(form);
            formData.forEach((value, key) => 
            {
                if(item.hasOwnProperty(key))
                {
                    const input = form.querySelector(`[name="${key}"]`);
                    if(input)
                    {
                        input.value = item[key];
                    }
                }
            });
        } catch (error) 
        {
            console.error('Failed to load form data:', error);
        }
    });

    const tables = document.querySelectorAll('table[data-api-endpoint]');
    tables.forEach(async (table) => 
    {
        await handleTable(table);
    });
});