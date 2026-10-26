const fs = require("fs");
const path = require("path");

const root = path.join(__dirname, "../resources/js/components/Dashboard");
const reps = [
    [/placeholder="Search user"/g, ':placeholder="dt.common.search_user"'],
    [/placeholder="Search item \.\.\."/g, ':placeholder="dt.common.search_item"'],
    [/placeholder="Search Item \.\.\."/g, ':placeholder="dt.common.search_item"'],
    [/placeholder="Search \.\.\."/g, ':placeholder="dt.common.search"'],
    [/placeholder="Search conversations\.\.\."/g, ':placeholder="dt.common.search_conversations"'],
    [/>Delete Selected</g, ">{{ dt.common.delete_selected }}<"],
    [/please wait \.\.\./g, "{{ dt.common.please_wait }}"],
    [
        /<span class="me-2" >\{\{ selectedItems\.length \}\}<\/span>Selected/g,
        '<span class="me-2" >{{ selectedItems.length }}</span>{{ dt.common.selected }}',
    ],
    [
        /<span class="me-2">\{\{ selectedItems\.length \}\}<\/span>Selected/g,
        '<span class="me-2">{{ selectedItems.length }}</span>{{ dt.common.selected }}',
    ],
    [/<i class="ki-duotone ki-plus fs-2"><\/i>Add New<\/button>/g, '<i class="ki-duotone ki-plus fs-2"></i>{{ dt.common.add_new }}</button>'],
    [/<th class="min-w-125px">User<\/th>/g, '<th class="min-w-125px">{{ dt.common.user }}</th>'],
    [/<th class="min-w-125px">Status<\/th>/g, '<th class="min-w-125px">{{ dt.common.status }}</th>'],
    [/<th class="min-w-125px">Actions<\/th>/g, '<th class="text-center min-w-100px">{{ dt.common.actions }}</th>'],
    [/<th class="text-center min-w-100px">Actions<\/th>/g, '<th class="text-center min-w-100px">{{ dt.common.actions }}</th>'],
    [/<th class="min-w-125px">Role<\/th>/g, '<th class="min-w-125px">{{ dt.common.role }}</th>'],
    [/<th class="min-w-125px">Joined Date<\/th>/g, '<th class="min-w-125px">{{ dt.common.joined_date }}</th>'],
    [/<th class="min-w-125px">Created Date<\/th>/g, '<th class="min-w-125px">{{ dt.common.created_date }}</th>'],
    [/<th class="min-w-125px">Type<\/th>/g, '<th class="min-w-125px">{{ dt.common.type }}</th>'],
    [/<th class="min-w-125px">Name<\/th>/g, '<th class="min-w-125px">{{ dt.common.name }}</th>'],
    [/<th class="min-w-125px">description<\/th>/g, '<th class="min-w-125px">{{ dt.common.description }}</th>'],
    [/<i class="ki-duotone ki-plus fs-2"><\/i>Add Admin<\/button>/g, '<i class="ki-duotone ki-plus fs-2"></i>{{ dt.common.add_admin }}</button>'],
    [/<i class="ki-duotone ki-plus fs-2"><\/i>Add User<\/button>/g, '<i class="ki-duotone ki-plus fs-2"></i>{{ dt.common.add_user }}</button>'],
    [/<i class="ki-duotone ki-plus fs-2"><\/i>Add New Item<\/button>/g, '<i class="ki-duotone ki-plus fs-2"></i>{{ dt.common.add_new_item }}</button>'],
    [/<\/i>Export<\/button>/g, '</i>{{ dt.common.export }}</button>'],
    [/<h2 class="fw-bold">Add User<\/h2>/g, '<h2 class="fw-bold">{{ dt.common.add_user }}</h2>'],
    [/<h2 class="fw-bold">Edit password<\/h2>/g, '<h2 class="fw-bold">{{ dt.users.edit_password }}</h2>'],
    [/<h2 class="fw-bold">Add Edit Item<\/h2>/g, '<h2 class="fw-bold">{{ dt.common.add_edit_item }}</h2>'],
    [/<h2 class="fw-bold">Add Edit Notification<\/h2>/g, '<h2 class="fw-bold">{{ dt.common.add_edit_notification }}</h2>'],
    [/<td >Not Decided<\/td>/g, '<td>{{ dt.common.not_decided }}</td>'],
    [/<td v-if="item.parent_id">Item<\/td>/g, '<td v-if="item.parent_id">{{ dt.common.item }}</td>'],
    [/<td v-else>Group<\/td>/g, '<td v-else>{{ dt.common.group }}</td>'],
];

function walk(dir) {
    for (const f of fs.readdirSync(dir)) {
        const p = path.join(dir, f);
        if (fs.statSync(p).isDirectory()) {
            walk(p);
        } else if (p.endsWith(".vue")) {
            let s = fs.readFileSync(p, "utf8");
            const o = s;
            for (const [a, b] of reps) {
                s = s.replace(a, b);
            }
            if (s !== o) {
                fs.writeFileSync(p, s);
                console.log("updated", path.relative(root, p));
            }
        }
    }
}

walk(root);
