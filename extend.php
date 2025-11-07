<?php
return [
    (new Flarum\Extend\Frontend('forum'))
        ->content(function (\Flarum\Frontend\Document $document) {
            $document->head[] = <<<'HTML'
<script>
console.log("🔥 Subtags завантажено");

(function() {
    let observer = null;
    let retryCount = 0;
    const maxRetries = 10;
    
    function initSubtags() {
        const waitForApp = setInterval(function() {
            if (typeof app !== "undefined" && app.store && app.route && m) {
                clearInterval(waitForApp);
                console.log("✅ App готовий");
                startWatching();
            }
        }, 50);
        
        setTimeout(function() { clearInterval(waitForApp); }, 10000);
    }
    
    function startWatching() {
        observer = new MutationObserver(function(mutations) {
            checkAndShowSubtags();
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        setTimeout(checkAndShowSubtags, 100);
        setTimeout(checkAndShowSubtags, 500);
        setTimeout(checkAndShowSubtags, 1000);
    }
    
    function checkAndShowSubtags() {
        const sidebar = document.querySelector('.IndexPage-nav');
        if (!sidebar) {
            return;
        }
        
        const childTags = sidebar.querySelectorAll('.TagLinkButton.child');
        if (childTags.length === 0) {
            retryCount++;
            if (retryCount < maxRetries) {
                setTimeout(checkAndShowSubtags, 300);
            }
            return;
        }
        
        showSubtags();
    }
    
    function showSubtags() {
        try {
            if (document.querySelector('.subtags-display')) {
                return;
            }
            
            const sidebar = document.querySelector('.IndexPage-nav');
            if (!sidebar) return;
            
            const childTags = sidebar.querySelectorAll('.TagLinkButton.child');
            if (childTags.length === 0) return;
            
            const currentUrl = window.location.pathname;
            let isOnChildTag = false;
            
            childTags.forEach(function(tag) {
                const href = tag.getAttribute('href');
                if (href && currentUrl.includes(href)) {
                    isOnChildTag = true;
                }
            });
            
            if (isOnChildTag) return;
            
            let container = document.querySelector('.IndexPage-results');
            if (!container) container = document.querySelector('.DiscussionList');
            if (!container) container = document.querySelector('.IndexPage-toolbar');
            if (!container) return;
            
            const subtagsDiv = document.createElement('div');
            subtagsDiv.className = 'subtags-display';
            
            const buttonsArray = [];
            childTags.forEach(function(tag) {
                const href = tag.getAttribute('href');
                const style = tag.getAttribute('style');
                const labelEl = tag.querySelector('.Button-label');
                const name = labelEl ? labelEl.textContent.trim() : '';
                
                if (name && href) {
                    buttonsArray.push(
                        m('a.subtag-item', {
                            href: href,
                            style: style,
                            onclick: function(e) {
                                e.preventDefault();
                                removeSubtags();
                                m.route.set(href);
                            }
                        }, m('span.subtag-label', name))
                    );
                }
            });
            
            m.render(subtagsDiv, 
                m('div', {
                    style: {
                        padding: '16px 0',
                        marginBottom: '16px',
                        borderBottom: '1px solid var(--control-bg)'
                    }
                }, [
                    m('div', {
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
                                color: 'var(--muted-color)',
                                marginRight: '8px'
                            }
                        }, '📂 '),
                        ...buttonsArray
                    ])
                ])
            );
            
            if (container.classList.contains('IndexPage-toolbar')) {
                container.parentNode.insertBefore(subtagsDiv, container.nextSibling);
            } else {
                container.insertBefore(subtagsDiv, container.firstChild);
            }
            
        } catch (e) {
            console.error("❌ Помилка:", e);
        }
    }
    
    function removeSubtags() {
        const oldBlock = document.querySelector('.subtags-display');
        if (oldBlock) oldBlock.remove();
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSubtags);
    } else {
        initSubtags();
    }
})();
</script>

<style>
.subtag-item {
    text-decoration: none;
    transition: all 0.3s ease;
}

.subtag-item:hover {
    transform: translateY(-2px);
}

.subtag-label {
    padding: 4px 8px;
    border-radius: 16px;
    background-color: var(--body-bg-faded);
    color: var(--tag-color);
    font-size: 16px;
    font-weight: 500;
    box-shadow: 0px 0px 1px 1px var(--button-toggled-bg);
    transition: all 0.3s ease;
}

.subtag-item:hover .subtag-label {
    background-color: var(--tag-bg);
    box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.2);
    transform: scale(1.05);
}
</style>
HTML;
        })
]; 
