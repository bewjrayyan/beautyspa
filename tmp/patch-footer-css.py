from pathlib import Path

p = Path("/Applications/XAMPP/xamppfiles/htdocs/fleetcart/public/build/assets/main-BwFsVlUN-v4.7.72.css")
text = p.read_text()
old = ".main-footer{position:absolute;bottom:0;display:flex;align-items:center;justify-content:space-between;background:#fff;padding:16px 20px"
new = ".main-footer{position:fixed;bottom:0;z-index:1040;display:flex;align-items:center;justify-content:space-between;background:#fff;padding:16px 20px"
print("exists", p.exists())
print("size", p.stat().st_size if p.exists() else 0)
print("matches", text.count(old))
i = text.find(".main-footer{position:")
print("found", i)
if i != -1:
    print(repr(text[i:i + 220]))
if text.count(old) == 1:
    p.write_text(text.replace(old, new, 1))
    print("updated")
else:
    print("not updated")
