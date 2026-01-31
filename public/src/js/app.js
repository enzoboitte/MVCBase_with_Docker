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
    const perPage = parseInt(table.getAttribute('data-per-page')) || 10;
    let currentPage = 1;
    let allData = [];
    
    try 
    {
        const data = await apiRequest('GET', endpoint);
        allData = data.data;
        if(!allData || allData.length === 0) return;
        
        const thead = table.querySelector('thead tr');
        thead.innerHTML = '';
        
        // populate table header
        const listKeys = Object.keys(allData[0]);

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
        
        // Create pagination container
        let paginationContainer = table.parentElement.querySelector('.table-pagination');
        if (!paginationContainer) {
            paginationContainer = document.createElement('div');
            paginationContainer.className = 'table-pagination';
            table.parentElement.insertBefore(paginationContainer, table.nextSibling);
        }
        
        function renderPage(page) {
            currentPage = page;
            const start = (page - 1) * perPage;
            const end = start + perPage;
            const pageData = allData.slice(start, end);
            const totalPages = Math.ceil(allData.length / perPage);
            
            // populate table with data
            const tbody = table.querySelector('tbody');
            tbody.innerHTML = '';
            pageData.forEach(item => 
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
                        const editUrl = table.getAttribute('data-edit-url') || '/dashboard/diploma/edit';
                        window.location.href = `${editUrl}/${item.id}`;
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
                                // Remove from allData and re-render
                                allData = allData.filter(d => d.id !== item.id);
                                if (currentPage > Math.ceil(allData.length / perPage) && currentPage > 1) {
                                    currentPage--;
                                }
                                renderPage(currentPage);
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
            
            // Render pagination
            paginationContainer.innerHTML = '';
            if (totalPages > 1) {
                const info = document.createElement('span');
                info.className = 'pagination-info';
                info.textContent = `Page ${page} / ${totalPages} (${allData.length} éléments)`;
                paginationContainer.appendChild(info);
                
                const buttons = document.createElement('div');
                buttons.className = 'pagination-buttons';
                
                // Previous button
                const prevBtn = document.createElement('button');
                prevBtn.textContent = '←';
                prevBtn.disabled = page === 1;
                prevBtn.addEventListener('click', () => renderPage(page - 1));
                buttons.appendChild(prevBtn);
                
                // Page numbers
                const maxVisiblePages = 5;
                let startPage = Math.max(1, page - Math.floor(maxVisiblePages / 2));
                let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
                if (endPage - startPage < maxVisiblePages - 1) {
                    startPage = Math.max(1, endPage - maxVisiblePages + 1);
                }
                
                if (startPage > 1) {
                    const firstBtn = document.createElement('button');
                    firstBtn.textContent = '1';
                    firstBtn.addEventListener('click', () => renderPage(1));
                    buttons.appendChild(firstBtn);
                    if (startPage > 2) {
                        const dots = document.createElement('span');
                        dots.textContent = '...';
                        dots.className = 'pagination-dots';
                        buttons.appendChild(dots);
                    }
                }
                
                for (let i = startPage; i <= endPage; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.textContent = i;
                    pageBtn.className = i === page ? 'active' : '';
                    pageBtn.addEventListener('click', () => renderPage(i));
                    buttons.appendChild(pageBtn);
                }
                
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        const dots = document.createElement('span');
                        dots.textContent = '...';
                        dots.className = 'pagination-dots';
                        buttons.appendChild(dots);
                    }
                    const lastBtn = document.createElement('button');
                    lastBtn.textContent = totalPages;
                    lastBtn.addEventListener('click', () => renderPage(totalPages));
                    buttons.appendChild(lastBtn);
                }
                
                // Next button
                const nextBtn = document.createElement('button');
                nextBtn.textContent = '→';
                nextBtn.disabled = page === totalPages;
                nextBtn.addEventListener('click', () => renderPage(page + 1));
                buttons.appendChild(nextBtn);
                
                paginationContainer.appendChild(buttons);
            }
        }
        
        renderPage(1);
        
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