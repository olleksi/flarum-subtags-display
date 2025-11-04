<?php
return [
    (new Flarum\Extend\Frontend('forum'))
        ->content(function (\Flarum\Frontend\Document $document) {
            $document->head[] = <<<'HTML'
<script>
console.log("🔥 Subtags завантажено");

(function() {
    let observer = null;
    
    function initSubtags() {
        const waitForApp = setInterval(function() {
            if (typeof app !== "undefined" && app.store && app.route && m) {
                clearInterval(waitForApp);
                console.log("✅ App готовий");
                startWatching();
            }
        }, 50);
        
        setTimeout(function() { clearInterval(waitForApp); }, 5000);
    }
    
    function startWatching() {
        // Спостерігаємо за змінами в body
        observer = new MutationObserver(function(mutations) {
            // Перевіряємо чи з'явилася бокова панель з дочірніми тегами
            const sidebar = document.querySelector('.IndexPage-nav');
            if (sidebar) {
                const childTags = sidebar.querySelectorAll('.TagLinkButton.child');
                if (childTags.length > 0) {
                    // Якщо знайшли дочірні теги в боковій панелі - показуємо їх нагорі
                    const existing = document.querySelector('.subtags-display');
                    if (!existing) {
                        console.log("👶 Бокова панель готова з", childTags.length, "тегами");
                        showSubtags();
                    }
                }
            }
        });
        
        // Починаємо спостереження
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Також перевіряємо одразу
        setTimeout(showSubtags, 100);
    }
    
    function showSubtags() {
        try {
            // Перевіряємо чи вже є блок
            if (document.querySelector('.subtags-display')) {
                return;
            }
            
            // Шукаємо бокову панель
            const sidebar = document.querySelector('.IndexPage-nav');
            if (!sidebar) {
                console.log("⏳ Бокова панель ще не готова");
                return;
            }
            
            // Шукаємо дочірні теги
            const childTags = sidebar.querySelectorAll('.TagLinkButton.child');
            if (childTags.length === 0) {
                console.log("👶 Немає дочірніх тегів");
                return;
            }
            
            // Перевіряємо чи поточна сторінка є одним з дочірніх тегів
            const currentUrl = window.location.pathname;
            let isOnChildTag = false;
            
            childTags.forEach(function(tag) {
                const href = tag.getAttribute('href');
                if (href && currentUrl.includes(href)) {
                    isOnChildTag = true;
                    console.log("👶 Знаходимось на дочірньому тезі:", tag.textContent.trim());
                }
            });
            
            if (isOnChildTag) {
                console.log("🚫 Суб-теги не показуємо, бо відкрито дочірній тег");
                return;
            }
            
            console.log("✅ Знайдено", childTags.length, "дочірніх тегів у боковій панелі");
            
            // Знаходимо контейнер для вставки
            const container = document.querySelector('.IndexPage-results, .DiscussionList');
            if (!container) {
                console.log("⏳ Контейнер для дискусій ще не готовий");
                return;
            }
            
            // Створюємо блок з суб-тегами
            const subtagsDiv = document.createElement('div');
            subtagsDiv.className = 'subtags-display';
            
            const buttonsArray = [];
            childTags.forEach(function(tag) {
                const href = tag.getAttribute('href');
                const style = tag.getAttribute('style');
                const labelEl = tag.querySelector('.Button-label');
                const iconEl = tag.querySelector('.TagIcon');
                const name = labelEl ? labelEl.textContent.trim() : '';
                
                if (name && href) {
                    const iconStyle = iconEl ? iconEl.getAttribute('style') : '';
                    
                    buttonsArray.push(
                        m('a.TagLinkButton.child.hasIcon', {
                            href: href,
                            style: style,
                            onclick: function(e) {
                                e.preventDefault();
                                removeSubtags();
                                m.route.set(href);
                            }
                        }, [
                            m('span.Button-icon.icon.TagIcon', {
                                style: iconStyle
                            }),
                            m('span.Button-label', name)
                        ])
                    );
                }
            });
            
            m.render(subtagsDiv, 
                m('div', {
                    style: {
                        padding: '16px 0',
                        marginBottom: '16px',
                        borderBottom: '1px solid var(--control-bg, #f3f4f5)'
                    }
                }, [
                    m('div', {
                        className: 'container',
                        style: {
                            display: 'flex',
                            alignItems: 'center',
                            gap: '8px',
                            flexWrap: 'wrap'
                        }
                    }, [
                        m('span', {
                            style: {
                                fontSize: '14px',
                                fontWeight: '500',
                                color: 'var(--muted-color, #999)',
                                marginRight: '8px'
                            }
                        }, '📂 '),
                        ...buttonsArray
                    ])
                ])
            );
            
            container.insertBefore(subtagsDiv, container.firstChild);
            console.log("✅ Суб-теги додано нагору сторінки!");
            
        } catch (e) {
            console.error("❌ Помилка:", e);
        }
    }
    
    function removeSubtags() {
        const oldBlock = document.querySelector('.subtags-display');
        if (oldBlock) {
            oldBlock.remove();
            console.log("🗑️ Старі суб-теги видалено");
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSubtags);
    } else {
        initSubtags();
    }
})();
</script>

<style>
.subtags-display .TagLinkButton {
    margin: 0 2px 2px 0;
    font-size: 17px;
 
 padding: 2px 6px;
  border-radius: 17px;
  box-shadow: 0px 0px 0px 1px var(--button-toggled-color);
}
</style>
HTML;
        })
];
