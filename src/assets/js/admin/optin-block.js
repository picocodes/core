(function(blocks, editor, __, element, components) {
    var el = element.createElement;
    var InspectorControls = editor.InspectorControls;
    var SelectControl = components.SelectControl;

    blocks.registerBlockType('mailoptin/email-optin', {
        title: __('MailOptin', 'mailoptin'),
        icon: MailOptinBlocks.icon,
        category: 'embed',
        attributes: {
            id: {
                type: 'number',
                default: MailOptinBlocks.defaultForm,
            },
        },
        edit: function(props) {

            var attributes = props.attributes;

            return [
                el(InspectorControls, { key: 'controls' },
                    el(SelectControl, {
                        value: attributes.id,
                        label: __('Select Form', 'mailoptin'),
                        type: 'select',
                        options: MailOptinBlocks.formOptions,
                        onChange: function(value) {
                            props.setAttributes({ id: value });
                        }
                    }),

                ),

                el('div', {
                    className: props.className,
                    dangerouslySetInnerHTML: { __html: MailOptinBlocks.templates[attributes.id].template },
                })
            ]
        },

        save: function(props) {
            var id = props.attributes.id;
            return el('div', {
                    className: props.className
                },
                MailOptinBlocks.templates[id].value
            )

        },
    });

})(
    window.wp.blocks,
    window.wp.editor,
    window.wp.i18n.__,
    window.wp.element,
    window.wp.components
);
