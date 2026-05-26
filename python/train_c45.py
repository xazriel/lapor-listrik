# -*- coding: utf-8 -*-
import sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

"""
====================================================
  SCRIPT TRAINING C4.5 - SISTEM LAPOR LISTRIK
  Desa Tanjung Durian
====================================================
Algoritma : C4.5 (Decision Tree, criterion=entropy)
Library   : scikit-learn, pandas, numpy, matplotlib
Tujuan    : Menghasilkan rules klasifikasi urgensi
            gangguan listrik dari data training.
====================================================
"""

import pandas as pd
import numpy as np
from sklearn.tree import DecisionTreeClassifier, export_text
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split, cross_val_score, StratifiedKFold
from sklearn.metrics import (
    accuracy_score, confusion_matrix,
    classification_report
)
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
import warnings
import os

warnings.filterwarnings('ignore')

# ─────────────────────────────────────────────
# 1. MUAT DATASET
# ─────────────────────────────────────────────
script_dir = os.path.dirname(os.path.abspath(__file__))
dataset_path = os.path.join(script_dir, 'dataset.csv')
df = pd.read_csv(dataset_path)

print("=" * 55)
print("  TRAINING MODEL C4.5 — LAPOR LISTRIK")
print("=" * 55)
print(f"\n[1] Dataset dimuat: {len(df)} baris, {len(df.columns)-1} atribut")
print("\nDistribusi Kelas:")
for kelas, jumlah in df['urgensi'].value_counts().items():
    pct = jumlah / len(df) * 100
    print(f"   {kelas:10s}: {jumlah:3d} data ({pct:.1f}%)")

# ─────────────────────────────────────────────
# 2. PREPROCESSING — DISKRETISASI DURASI
# ─────────────────────────────────────────────
def diskretisasi_durasi(jam):
    """Mengubah durasi numerik ke kategori ordinal."""
    if jam <= 2:
        return 0   # Pendek (≤ 2 jam)
    elif jam <= 5:
        return 1   # Sedang (3–5 jam)
    else:
        return 2   # Panjang (≥ 6 jam)

df['durasi_kategori'] = df['durasi_padam'].apply(diskretisasi_durasi)

# ─────────────────────────────────────────────
# 3. ENCODING FITUR KATEGORIK
# ─────────────────────────────────────────────
le_jenis  = LabelEncoder()
le_dampak = LabelEncoder()
le_label  = LabelEncoder()

df['jenis_enc']  = le_jenis.fit_transform(df['jenis_gangguan'])
df['dampak_enc'] = le_dampak.fit_transform(df['dampak_wilayah'])
df['label_enc']  = le_label.fit_transform(df['urgensi'])

print("\nEncoding Jenis Gangguan:")
for cls, enc in zip(le_jenis.classes_, range(len(le_jenis.classes_))):
    print(f"   {enc} = {cls}")

print("\nEncoding Dampak Wilayah:")
for cls, enc in zip(le_dampak.classes_, range(len(le_dampak.classes_))):
    print(f"   {enc} = {cls}")

# ─────────────────────────────────────────────
# 4. SPLIT DATA — 80% TRAINING, 20% TESTING
# ─────────────────────────────────────────────
X = df[['jenis_enc', 'dampak_enc', 'durasi_kategori']].values
y = df['label_enc'].values

X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

print(f"\n[2] Split Data:")
print(f"   Training : {len(X_train)} baris")
print(f"   Testing  : {len(X_test)} baris")

# ─────────────────────────────────────────────
# 5. TRAINING MODEL C4.5
#    criterion='entropy' = Information Gain (C4.5)
# ─────────────────────────────────────────────
model = DecisionTreeClassifier(
    criterion='entropy',   # C4.5 menggunakan Information Gain
    max_depth=6,           # Batasi kedalaman pohon
    min_samples_split=2,
    min_samples_leaf=1,
    random_state=42
)
model.fit(X_train, y_train)
print("\n[3] Model C4.5 berhasil dilatih!")

# ─────────────────────────────────────────────
# 6. EVALUASI MODEL
# ─────────────────────────────────────────────
y_pred_train = model.predict(X_train)
y_pred_test  = model.predict(X_test)

acc_train = accuracy_score(y_train, y_pred_train) * 100
acc_test  = accuracy_score(y_test,  y_pred_test)  * 100

# Cross-Validation 5-Fold
cv = StratifiedKFold(n_splits=5, shuffle=True, random_state=42)
cv_scores = cross_val_score(model, X, y, cv=cv, scoring='accuracy')
acc_cv    = cv_scores.mean() * 100

print("\n" + "-" * 55)
print("[4] HASIL EVALUASI MODEL")
print("-" * 55)
print(f"   Akurasi Training     : {acc_train:.2f}%")
print(f"   Akurasi Testing      : {acc_test:.2f}%")
print(f"   Akurasi CV (5-Fold)  : {acc_cv:.2f}%  ± {cv_scores.std()*100:.2f}%")
print(f"   CV Per-Fold          : {[f'{s*100:.1f}%' for s in cv_scores]}")

# ─────────────────────────────────────────────
# 7. CONFUSION MATRIX
# ─────────────────────────────────────────────
cm = confusion_matrix(y_test, y_pred_test)
class_names = le_label.classes_

print("\n[5] CONFUSION MATRIX (Data Testing)")
print(f"{'':>12}", end='')
for cn in class_names:
    print(f"  {cn:>6}", end='')
print()
for i, cn_row in enumerate(class_names):
    print(f"  {cn_row:>10}", end='')
    for val in cm[i]:
        print(f"  {val:>6}", end='')
    print()

# ─────────────────────────────────────────────
# 8. CLASSIFICATION REPORT
# ─────────────────────────────────────────────
print("\n[6] CLASSIFICATION REPORT")
print("-" * 55)
print(classification_report(
    y_test, y_pred_test,
    target_names=class_names
))

# ─────────────────────────────────────────────
# 9. POHON KEPUTUSAN (TEXT)
# ─────────────────────────────────────────────
feature_names = ['jenis_gangguan', 'dampak_wilayah', 'durasi_kategori']
tree_text = export_text(model, feature_names=feature_names)

print("[7] POHON KEPUTUSAN (Rules)")
print("-" * 55)
print(tree_text)

# ─────────────────────────────────────────────
# 10. SIMPAN GAMBAR CONFUSION MATRIX
# ─────────────────────────────────────────────
output_dir = os.path.join(script_dir, 'output')
os.makedirs(output_dir, exist_ok=True)

fig, ax = plt.subplots(figsize=(7, 5))
fig.patch.set_facecolor('#0f172a')
ax.set_facecolor('#1e293b')

im = ax.imshow(cm, interpolation='nearest', cmap='Blues')
ax.set_xticks(range(len(class_names)))
ax.set_yticks(range(len(class_names)))
ax.set_xticklabels(class_names, color='white', fontsize=11, fontweight='bold')
ax.set_yticklabels(class_names, color='white', fontsize=11, fontweight='bold')
ax.set_xlabel('Prediksi', color='#94a3b8', fontsize=12)
ax.set_ylabel('Aktual', color='#94a3b8', fontsize=12)
ax.set_title('Confusion Matrix — Model C4.5', color='white', fontsize=13, fontweight='bold', pad=15)
ax.tick_params(colors='#64748b')
for spine in ax.spines.values():
    spine.set_edgecolor('#334155')

thresh = cm.max() / 2.
for i in range(cm.shape[0]):
    for j in range(cm.shape[1]):
        color = 'white' if cm[i, j] > thresh else '#1e293b'
        ax.text(j, i, str(cm[i, j]),
                ha='center', va='center',
                fontsize=16, fontweight='black', color=color)

plt.tight_layout()
cm_path = os.path.join(output_dir, 'confusion_matrix.png')
plt.savefig(cm_path, dpi=150, bbox_inches='tight', facecolor='#0f172a')
plt.close()
print(f"\n✅ Confusion Matrix disimpan: {cm_path}")

# ─────────────────────────────────────────────
# 11. SIMPAN LAPORAN KE FILE TXT
# ─────────────────────────────────────────────
report_path = os.path.join(output_dir, 'hasil_training.txt')
with open(report_path, 'w', encoding='utf-8') as f:
    f.write("=" * 55 + "\n")
    f.write("  LAPORAN TRAINING MODEL C4.5\n")
    f.write("  Sistem Lapor Listrik — Desa Tanjung Durian\n")
    f.write("=" * 55 + "\n\n")
    f.write(f"Total Data Training : {len(X_train)}\n")
    f.write(f"Total Data Testing  : {len(X_test)}\n")
    f.write(f"Total Atribut       : 3 (Jenis, Dampak, Durasi)\n\n")
    f.write(f"Akurasi Training    : {acc_train:.2f}%\n")
    f.write(f"Akurasi Testing     : {acc_test:.2f}%\n")
    f.write(f"Akurasi CV (5-Fold) : {acc_cv:.2f}%\n\n")
    f.write("Confusion Matrix:\n")
    f.write(f"{'':>12}")
    for cn in class_names:
        f.write(f"  {cn:>6}")
    f.write("\n")
    for i, cn_row in enumerate(class_names):
        f.write(f"  {cn_row:>10}")
        for val in cm[i]:
            f.write(f"  {val:>6}")
        f.write("\n")
    f.write("\n" + tree_text)

print(f"✅ Laporan disimpan : {report_path}")
print("\n" + "=" * 55)
print("  SELESAI — Salin nilai akurasi ke config/c45_model.php")
print("=" * 55)
