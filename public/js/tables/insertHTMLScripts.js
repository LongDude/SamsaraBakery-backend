function insertHtmlAndScripts(container, header, html) {
    // Создаём временный контейнер для поиска скриптов
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;

    let twigHeader = tempDiv.querySelector('#twigHeaderContainer');
    if (twigHeader) {
        header.innerHTML = twigHeader.innerHTML;
    }

    let content = tempDiv.querySelector('#twigHTMLContainer');
    if (!content) {
        console.error('Template content not found');
        return;
    }

    container.innerHTML = content.innerHTML;
    // Подключаем все внешние скрипты
    tempDiv.querySelectorAll('script[src]').forEach(scriptTag => {
        const src = scriptTag.getAttribute('src');
        // Удаляем старый скрипт, если он есть
        const oldScript = document.getElementById('dynamic-table-script');
        if (oldScript) oldScript.remove();

        // Добавляем новый скрипт
        const newScript = document.createElement('script');
        newScript.src = src;
        newScript.type = scriptTag.type || 'text/javascript';
        newScript.id = 'dynamic-table-script';
        document.body.appendChild(newScript);
    });

    // Если есть инлайновые скрипты — можно выполнить их через eval (не рекомендуется для больших скриптов)
    tempDiv.querySelectorAll('script:not([src])').forEach(scriptTag => {
        try {
            eval(scriptTag.innerText);
        } catch (e) {
            console.error('Ошибка выполнения скрипта:', e);
        }
    });
}