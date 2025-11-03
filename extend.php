<?php

return [
    (new Flarum\Extend\Frontend('forum'))
        ->content(function (\Flarum\Frontend\Document $document) {
            $document->head[] = <<<'HTML'
<script>
console.log("🔥 Subtags v7 (Flarum Buttons) завантажено");

document.addEventListener('DOMContentLoaded', function() {
    initSubtags();
});

if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(initSubtags, 500);
}

function initSubtags() {
    let checkCount = 0;
    const checkInterval = setInterval(function() {
        checkCount++;
        
        if (typeof app !== "undefined" && app.store && app.route && m) {
            clearInterval(checkInterval);
            console.log("✅ App готовий (SPA mode)");
            startWatching();
        } else if (checkCount > 200) {
            clearInterval(checkInterval);
        }
    }, 100);
}

function startWatching() {
    console.log("👀 SPA навігація активована");
    
    function showSubtags() {
        setTimeout(function() {
            try {
                const url = window.location.pathname;
                
                if (url.includes('/t/')) {
                    const tagSlug = url.split('/t/')[1].split('/')[0];
                    console.log("🔍 Тег:", tagSlug);
                    
                    let currentTag = null;
                    if (app.store && app.store.all) {
                        const allTags = app.store.all('tags');
                        currentTag = allTags.find(function(tag) {
                            return tag.slug() === tagSlug;
                        });
                    }
                    
                    if (currentTag) {
                        const children = currentTag.children ? currentTag.children() : [];
                        console.log("👶 Дочірніх:", children.length);
                        
                        if (children.length > 0) {
                            const container = document.querySelector('.DiscussionListPane, .IndexPage-results, .DiscussionList');
                            
                            if (container) {
                                const oldBlock = document.querySelector('.subtags-display');
                                if (oldBlock) oldBlock.remove();
                                
                                // Створюємо Mithril компонент у стилі кнопок Flarum
                                const subtagsDiv = document.createElement('div');
                                subtagsDiv.className = 'subtags-display';
                                
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
                                            }, '📂 Підкатегорії:'),
                                            ...children.map(function(child) {
                                                const tagColor = child.color() || '#888';
                                                
                                                return m('a', {
                                                    href: app.route('tag', {tags: child.slug()}),
                                                    className: 'Button Button--link hasIcon',
                                                    style: {
                                                        backgroundColor: 'transparent',
                                                        color: tagColor,
                                                        padding: '8px 13px',
                                                        borderRadius: '4px',
                                                        textDecoration: 'none',
                                                        fontSize: '14px',
                                                        fontWeight: '500',
                                                        display: 'inline-flex',
                                                        alignItems: 'center',
                                                        transition: 'all 0.2s ease',
                                                        border: '1px solid ' + tagColor,
                                                        cursor: 'pointer',
                                                        lineHeight: '1.5'
                                                    },
                                                    onclick: function(e) {
                                                        e.preventDefault();
                                                        console.log("🚀 SPA перехід до:", child.name());
                                                        m.route.set(app.route('tag', {tags: child.slug()}));
                                                    },
                                                    onmouseenter: function(e) {
                                                        e.target.style.textDecoration = 'underline';
                                                    },
                                                    onmouseleave: function(e) {
                                                        e.target.style.textDecoration = 'none';
                                                    }
                                                }, child.name());
                                            })
                                        ])
                                    ])
                                );
                                
                                container.insertBefore(subtagsDiv, container.firstChild);
                                console.log("✅ SPA суб-теги додано!");
                            }
                        }
                    }
                }
            } catch (e) {
                console.error("❌ Помилка:", e.message);
            }
        }, 1000);
    }
    
    // Запуск
    showSubtags();
    
    // Відстежування через MutationObserver
    let lastUrl = location.href;
    new MutationObserver(function() {
        const url = location.href;
        if (url !== lastUrl) {
            lastUrl = url;
            console.log("🔄 SPA навігація виявлена");
            showSubtags();
        }
    }).observe(document.body, { subtree: true, childList: true });
    
    console.log("✅ Готово до роботи!");
}
</script>
HTML;
        })
];
