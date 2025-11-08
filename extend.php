<?php

use Flarum\Extend;
use Flarum\Settings\SettingsRepositoryInterface;

return [
    (new Extend\Frontend('forum'))
        ->content(function (\Flarum\Frontend\Document $document) {
            $settings = resolve(SettingsRepositoryInterface::class);
            $hideSidebarSubtags = $settings->get('olleksi-subtags.hide_sidebar', false) ? 'true' : 'false';
            
            $document->head[] = '<script>
(function() {
    const hideSidebarSubtags = ' . $hideSidebarSubtags . ';
    let observer = null;
    let checkTimeout = null;
    let isProcessing = false;
    
    function initSubtags() {
        const waitForApp = setInterval(function() {
            if (typeof app !== "undefined" && app.store && m) {
                clearInterval(waitForApp);
                
                if (hideSidebarSubtags) {
                    applySidebarHiding();
                }
                
                setupRouteListener();
                startObserver();
                scheduleCheck();
            }
        }, 50);
        
        setTimeout(function() { clearInterval(waitForApp); }, 10000);
    }
    
    function applySidebarHiding() {
        const style = document.createElement("style");
        style.id = "subtags-sidebar-hide";
        style.textContent = ".IndexPage-nav .TagLinkButton.child { display: none !important; }";
        document.head.appendChild(style);
    }
    
    function setupRouteListener() {
        const originalRouteSet = m.route.set;
        m.route.set = function() {
            removeSubtags();
            isProcessing = false;
            
            const result = originalRouteSet.apply(this, arguments);
            scheduleCheck(200);
            
            return result;
        };
    }
    
    function startObserver() {
        if (observer) return;
        
        observer = new MutationObserver(function(mutations) {
            const hasRelevantChanges = mutations.some(function(mutation) {
                return Array.from(mutation.addedNodes).some(function(node) {
                    return node.nodeType === 1 && (
                        node.classList && (
                            node.classList.contains("TagLinkButton") ||
                            node.classList.contains("IndexPage-nav") ||
                            node.classList.contains("IndexPage-results")
                        ) ||
                        node.querySelector && (
                            node.querySelector(".TagLinkButton") ||
                            node.querySelector(".IndexPage-nav") ||
                            node.querySelector(".IndexPage-results")
                        )
                    );
                });
            });
            
            if (hasRelevantChanges) {
                scheduleCheck();
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    function scheduleCheck(delay) {
        if (checkTimeout) {
            clearTimeout(checkTimeout);
        }
        
        checkTimeout = setTimeout(function() {
            processSubtags();
        }, delay || 150);
    }
    
    function processSubtags() {
        if (isProcessing) return;
        
        const childTags = getChildTagsData();
        
        if (childTags.length === 0) {
            removeSubtags();
            return;
        }
        
        const currentUrl = window.location.pathname;
        const isOnChildTag = childTags.some(function(tag) {
            return currentUrl.includes(tag.href);
        });
        
        if (isOnChildTag) {
            removeSubtags();
            return;
        }
        
        isProcessing = true;
        renderSubtags(childTags);
        isProcessing = false;
    }
    
    function getChildTagsData() {
        const sidebar = document.querySelector(".IndexPage-nav");
        if (!sidebar) return [];
        
        const childTags = sidebar.querySelectorAll(".TagLinkButton.child");
        const tagsData = [];
        
        childTags.forEach(function(tag) {
            const href = tag.getAttribute("href");
            const style = tag.getAttribute("style");
            const labelEl = tag.querySelector(".Button-label");
            const name = labelEl ? labelEl.textContent.trim() : "";
            
            if (name && href) {
                tagsData.push({
                    name: name,
                    href: href,
                    style: style || ""
                });
            }
        });
        
        return tagsData;
    }
    
    function renderSubtags(childTagsData) {
        try {
            removeSubtags();
            
            let container = document.querySelector(".IndexPage-results");
            if (!container) container = document.querySelector(".DiscussionList");
            if (!container) container = document.querySelector(".IndexPage-toolbar");
            
            if (!container) return;
            
            const subtagsDiv = document.createElement("div");
            subtagsDiv.className = "subtags-display";
            
            const buttonsArray = childTagsData.map(function(tag) {
                return m("a.subtag-item", {
                    href: tag.href,
                    style: tag.style,
                    onclick: function(e) {
                        e.preventDefault();
                        removeSubtags();
                        m.route.set(tag.href);
                    }
                }, m("span.subtag-label", tag.name));
            });
            
            m.render(subtagsDiv, 
                m("div.subtags-container", [
                    m("div.subtags-wrapper", [
                        m("span.subtags-title", "📂"),
                        ...buttonsArray
                    ])
                ])
            );
            
            if (container.classList.contains("IndexPage-toolbar")) {
                container.parentNode.insertBefore(subtagsDiv, container.nextSibling);
            } else {
                container.insertBefore(subtagsDiv, container.firstChild);
            }
            
        } catch (e) {
            console.error("Subtags render error:", e);
        }
    }
    
    function removeSubtags() {
        const oldBlock = document.querySelector(".subtags-display");
        if (oldBlock) {
            oldBlock.remove();
        }
    }
    
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initSubtags);
    } else {
        initSubtags();
    }
})();
</script>

<style>
.subtags-container {
    padding: 16px 0;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--control-bg);
}

.subtags-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.subtags-title {
    font-size: 14px;
    font-weight: 500;
    color: var(--muted-color);
    margin-right: 8px;
}

.subtag-item {
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.subtag-item:hover {
    transform: translateY(-2px);
}

.subtag-label {
    padding: 6px 12px;
    border-radius: 16px;
    background-color: var(--body-bg-faded);
    color: var(--tag-color);
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0px 0px 1px 1px var(--button-toggled-bg);
    transition: all 0.3s ease;
    display: inline-block;
    white-space: nowrap;
}

.subtag-item:hover .subtag-label {
    background-color: var(--tag-bg);
    box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.2);
    transform: scale(1.05);
}

.subtags-display {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>';
        }),
    
    (new Extend\Frontend('admin'))
        ->content(function (\Flarum\Frontend\Document $document) {
            $document->head[] = '<script>
window.addEventListener("load", function() {
    setTimeout(function() {
        if (window.app && window.app.extensionData) {
            try {
                app.extensionData
                    .for("olleksi-subtags")
                    .registerSetting({
                        setting: "olleksi-subtags.hide_sidebar",
                        type: "boolean",
                        label: app.translator.trans("olleksi-subtags.admin.hide_sidebar_label"),
                        help: app.translator.trans("olleksi-subtags.admin.hide_sidebar_help")
                    });
                
                setTimeout(function() {
                    if (window.m && window.m.redraw) {
                        window.m.redraw();
                    }
                }, 100);
                
            } catch (error) {
                console.error("Subtags admin error:", error);
            }
        }
    }, 2000);
});
</script>';
        }),
    
    (new Extend\Locales(__DIR__.'/locale')),
    
    (new Extend\Settings())
        ->default('olleksi-subtags.hide_sidebar', false)
        ->serializeToForum('olleksi-subtags.hide_sidebar', 'olleksi-subtags.hide_sidebar', 'boolval')
];
