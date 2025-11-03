<?php
return [
    (new Flarum\Extend\Frontend('forum'))
        ->content(function (Flarum\Frontend\Document $document) {
            $document->head[] = '
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                setTimeout(function() {
                    if (window.app && window.app.current && window.app.current.get("currentTag")) {
                        const currentTag = window.app.current.get("currentTag");
                        if (currentTag.children && currentTag.children().length > 0) {
                            const children = currentTag.children();
                            const container = document.querySelector(".DiscussionListPane, .IndexPage-results");
                            
                            if (container) {
                                const oldBlock = document.querySelector(".subtags-display");
                                if (oldBlock) oldBlock.remove();
                                
                                const subtagsDiv = document.createElement("div");
                                subtagsDiv.className = "subtags-display";
                                subtagsDiv.innerHTML = \`
                                    <div style="padding: 16px 0; margin-bottom: 16px; border-bottom: 1px solid #f3f4f5;">
                                        <div class="container">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <span style="font-size: 14px; font-weight: 500; color: #999; margin-right: 8px;">📂 Підкатегорії:</span>
                                                ${children.map(child => \`
                                                    <a href="/t/\${child.slug()}" 
                                                       class="Button Button--link" 
                                                       style="color: \${child.color() || "#888"}; border: 1px solid \${child.color() || "#888"}; padding: 8px 13px; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                                        \${child.name()}
                                                    </a>
                                                \`).join("")}
                                            </div>
                                        </div>
                                    </div>
                                \`;
                                
                                container.insertBefore(subtagsDiv, container.firstChild);
                            }
                        }
                    }
                }, 1000);
            });
            </script>
            ';
        })
];
